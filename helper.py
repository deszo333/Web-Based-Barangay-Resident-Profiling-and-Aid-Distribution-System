import os

# Folders we don't need to see (to keep the list clean)
IGNORE_DIRS = ['.git', 'vendor', 'node_modules', 'fontawesome', 'images']
# File types we don't need to list
IGNORE_EXTS = ['.png', '.jpg', '.jpeg', '.gif', '.ico', '.mp4']

def map_directory():
    output_file = "project_files_list.txt"
    
    with open(output_file, 'w', encoding='utf-8') as f:
        f.write("=== PROJECT DIRECTORY MAP ===\n\n")
        
        for root, dirs, files in os.walk('.'):
            # Skip ignored directories
            dirs[:] = [d for d in dirs if not any(ign in d.lower() for ign in IGNORE_DIRS)]
            
            for file in files:
                # Skip media files
                if not any(file.lower().endswith(ext) for ext in IGNORE_EXTS):
                    # Format path nicely (convert Windows slashes to web slashes)
                    filepath = os.path.join(root, file).replace('\\', '/').replace('./', '')
                    f.write(f"{filepath}\n")
                    
    print(f"✅ Project mapped successfully!")
    print(f"📄 Open '{output_file}' and upload/paste the contents to the AI.")

if __name__ == "__main__":
    map_directory()