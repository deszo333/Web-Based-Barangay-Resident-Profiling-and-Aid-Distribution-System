let rfidPort;
let rfidReader;

// === NEW: DEBOUNCE VARIABLES ===
let lastScannedId = "";
let lastScanTime = 0;
const SCAN_COOLDOWN_MS = 3000; // 3 seconds before the same card can be read again

async function connectRFIDScanner(onScanCallback, buttonElement = null) {
    try {
        const availablePorts = await navigator.serial.getPorts();
        
        if (availablePorts.length > 0) {
            rfidPort = availablePorts[0]; 
        } else {
            rfidPort = await navigator.serial.requestPort();
        }

        await rfidPort.open({ baudRate: 9600 });
        console.log("RFID Scanner Connected!");
        
        // === NEW: UI CONNECTION FEEDBACK ===
        if (buttonElement){
            buttonElement.classList.remove("pulse");
            buttonElement.style.background = "#14AE5C"; // Turn green
            buttonElement.innerHTML = `<i class="fa-solid fa-check scan-icon" style="color:#fff;"></i>`;
            
            const statusText = document.getElementById("scan_status");
            if (statusText) statusText.textContent = "Scanner Active. Ready to Tap!";
        }

        readRFIDLoop(onScanCallback);
    } catch (error) {
        console.error("Connection failed:", error);
        // === REMOVED alert() -> UPGRADED TO POPUP ===
        Popup.open({
            title: "Scanner Disconnected",
            message: "Failed to connect to the RFID scanner. Please ensure it is plugged in and no other application (like the Arduino IDE) is using the port.",
            type: "danger"
        });
    }
}

async function readRFIDLoop(onScanCallback) {
    const textDecoder = new TextDecoderStream();
    const readableStreamClosed = rfidPort.readable.pipeTo(textDecoder.writable);
    rfidReader = textDecoder.readable.getReader();

    let buffer = ""; 

    try {
        while (true) {
            const { value, done } = await rfidReader.read();
            if (done) break;
            
            if (value) {
                buffer += value;
                if (buffer.includes("\n")) {
                    const rfidNumber = buffer.trim();
                    
                    // === NEW: HARDWARE DEBOUNCER LOGIC ===
                    const now = Date.now();
                    if (rfidNumber.length > 0) {
                        // Only trigger if it's a DIFFERENT card, or if 3 seconds have passed
                        if (rfidNumber !== lastScannedId || (now - lastScanTime) > SCAN_COOLDOWN_MS) {
                            lastScannedId = rfidNumber;
                            lastScanTime = now;
                            onScanCallback(rfidNumber);
                        } else {
                            console.log("Debounced duplicate scan ignored:", rfidNumber);
                        }
                    }
                    buffer = "";
                }
            }
        }
    } catch (error) {
        console.error("Reading error:", error);
    } finally {
        rfidReader.releaseLock();
    }
}