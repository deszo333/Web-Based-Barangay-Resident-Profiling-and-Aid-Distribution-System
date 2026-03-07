let rfidPort;
let rfidReader;

async function connectRFIDScanner(onScanCallback, buttonElement = null) {
    try {
        // 1. Check if Chrome already remembers the Arduino!
        const availablePorts = await navigator.serial.getPorts();
        
        if (availablePorts.length > 0) {
            rfidPort = availablePorts[0]; // Auto-select the remembered port!
        } else {
            rfidPort = await navigator.serial.requestPort(); // Show popup only if first time ever
        }

        await rfidPort.open({ baudRate: 9600 });
        console.log("RFID Scanner Connected!");
        
        if (buttonElement) {
            buttonElement.innerHTML = '<i class="fa-solid fa-check"></i> Scanner Active';
            buttonElement.style.backgroundColor = "#28a745"; 
            buttonElement.style.color = "white";
        }
        
        readRFIDLoop(onScanCallback);
        
    } catch (error) {
        console.error("Connection failed:", error);
        alert("Failed to connect. Make sure no other program (like the Arduino IDE) is using the port.");
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
                    if (rfidNumber.length > 0) {
                        onScanCallback(rfidNumber);
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