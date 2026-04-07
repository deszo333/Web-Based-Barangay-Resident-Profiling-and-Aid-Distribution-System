import os

# 1. Define the specific files you want to compile into the text file
target = [
    # --- Cypress Configs ---
    "cypress/support/commands.js",
    "cypress/support/e2e.js",
    "cypress.config.js", # (If you don't have this one yet, that's okay!)

    # --- Login Page ---
    "public/login.php"
]

# Folders to ignore when building the tree so it doesn't get cluttered
IGNORE_DIRS = {'.git', 'node_modules', '__pycache__', 'assets/images', 'fontawesome', 'vendor'}
OUTPUT_FILE = "compiled_codebase.txt"

def build_tree(startpath, prefix=""):
    """Recursively builds the project tree as a string."""
    tree_str = ""
    try:
        items = sorted(os.listdir(startpath))
    except PermissionError:
        return ""

    # Filter out ignored directories
    items = [i for i in items if i not in IGNORE_DIRS]
    
    for count, item in enumerate(items):
        path = os.path.join(startpath, item)
        is_last = count == (len(items) - 1)
        connector = "└── " if is_last else "├── "
        
        tree_str += f"{prefix}{connector}{item}\n"
        
        if os.path.isdir(path):
            extension = "    " if is_last else "│   "
            tree_str += build_tree(path, prefix=prefix + extension)
            
    return tree_str

def compile_code():
    with open(OUTPUT_FILE, 'w', encoding='utf-8') as out_file:
        
        # --- SECTION 1: PROJECT TREE ---
        out_file.write("=========================================\n")
        out_file.write("             PROJECT TREE                \n")
        out_file.write("=========================================\n")
        out_file.write(".\n")
        out_file.write(build_tree("."))
        out_file.write("\n\n")

        # --- SECTION 2: TARGET FILES ---
        out_file.write("=========================================\n")
        out_file.write("             TARGET FILES                \n")
        out_file.write("=========================================\n")
        
        for filepath in target:
            out_file.write(f"\n\n{'='*50}\n")
            out_file.write(f"📁 FILE: {filepath}\n")
            out_file.write(f"{'='*50}\n\n")
            
            if os.path.exists(filepath):
                try:
                    with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
                        out_file.write(f.read())
                except Exception as e:
                    out_file.write(f"[Error reading file: {e}]\n")
            else:
                out_file.write(f"[FILE NOT FOUND: Make sure the path is correct]\n")
                
        out_file.write("\n\n================ END OF COMPILATION ================\n")

if __name__ == "__main__":
    compile_code()
    print(f"✅ Compilation complete! Check the '{OUTPUT_FILE}' file.")