import os

# 1. Put the files you are currently working on here!
# Leave out files that have nothing to do with the current task.
TARGET_FILES = [
    "rfid-tags-insurance.php",
    "assets/js/rfid-tagss.js"
]

OUTPUT_FILE = "ai_context.txt"

def compile_for_ai():
    with open(OUTPUT_FILE, 'w', encoding='utf-8') as outfile:
        outfile.write("=== PROJECT CONTEXT FOR AI ===\n")
        outfile.write("Please analyze the following files for the current task.\n\n")
        
        for filepath in TARGET_FILES:
            if os.path.exists(filepath):
                outfile.write(f"\n{'='*50}\n")
                outfile.write(f"FILE: {filepath}\n")
                outfile.write(f"{'='*50}\n\n")
                
                with open(filepath, 'r', encoding='utf-8') as infile:
                    outfile.write(infile.read())
                    outfile.write("\n")
                print(f"Added: {filepath}")
            else:
                print(f"WARNING: Could not find {filepath}")
                
    print(f"\nSuccess! You can now upload '{OUTPUT_FILE}' to the AI.")

if __name__ == "__main__":
    compile_for_ai()