import os

# 1. List the exact files you want to extract
target = [
    # ROOT
    "index.php",

    # API
    "api/add_account_process.php",
    "api/add_aid_program.php",
    "api/add_household.php",
    "api/add_resident.php",
    "api/add_rfid_tags.php",
    "api/change-password-process.php",
    "api/delete_aid_program.php",
    "api/delete_household.php",
    "api/delete_resident.php",
    "api/delete_rfid_tag.php",
    "api/export_report.php",
    "api/fetch_recent_transactions.php",
    "api/filter_programs.php",
    "api/get_aid_type.php",
    "api/get_detailed_report.php",
    "api/get_householdinfo.php",
    "api/get_next_household_number.php",
    "api/process_scan.php",
    "api/search_account.php",
    "api/search_household.php",
    "api/search_resident.php",
    "api/toggle_program_state.php",
    "api/toggle_rfid_status.php",
    "api/toggle_user_status.php",
    "api/update_account_process.php",
    "api/update_resident.php",

    # CONFIG
    "config/auth_check.php",
    "config/database.php",
    "config/db_connect.php",

    # INCLUDES
    "includes/sidebar.php",

    # PAGES
    "pages/account-man.php",
    "pages/admin-dashboard.php",
    "pages/aid-program-setup.php",
    "pages/distribution-page.php",
    "pages/household-management.php",
    "pages/reports-logs.php",
    "pages/resident-profiling.php",
    "pages/rfid-tags-insurance.php",
    "pages/staff-dashboard.php",
    "pages/start-distribution.php",

    # PUBLIC
    "public/login.php",
    "public/logout.php",
    "public/signup.php",

    # JS FILES
    "assets/js/account-man.js",
    "assets/js/admins-dashboard.js",
    "assets/js/aid-programs-setup.js",
    "assets/js/chart.umd.min.js",
    "assets/js/distribution-page.js",
    "assets/js/household-managementss.js",
    "assets/js/login.js",
    "assets/js/reports-logs.js",
    "assets/js/resident-profilingss.js",
    "assets/js/rfid-tagss.js",
    "assets/js/rfid_scanner.js",
    "assets/js/script.js",
    "assets/js/start-distribution.js",

    # HTML (POPUP)
    "assets/popup/popup.html"
]

output_filename = "compiled_codebase.txt"

# 2. Folders to ignore so the tree doesn't get flooded
ignore_folders = {
    '.git', 
    'xampp', 
    'node_modules', 
    '__pycache__', 
    'Compiled_Installer',
    'Output',
    'fontawesome',   
    'webfonts',      
    'fonts'          
}

def generate_tree(dir_path, prefix=""):
    """Recursively maps out the folder structure."""
    try:
        items = sorted(os.listdir(dir_path))
    except PermissionError:
        return []
    
    # Filter out hidden files and ignored folders
    items = [item for item in items if item not in ignore_folders and not item.startswith('.')]
    
    tree_lines = []
    for i, item in enumerate(items):
        path = os.path.join(dir_path, item)
        is_last = (i == len(items) - 1)
        connector = "└── " if is_last else "├── "
        
        tree_lines.append(f"{prefix}{connector}{item}")
        
        if os.path.isdir(path):
            extension = "    " if is_last else "│   "
            tree_lines.extend(generate_tree(path, prefix=prefix + extension))
            
    return tree_lines

def compile_project():
    print(f"Starting compilation into {output_filename}...\n")
    
    with open(output_filename, 'w', encoding='utf-8') as outfile:
        
        # --- WRITE PROJECT TREE ---
        print("Mapping folder tree...")
        outfile.write("="*50 + "\n")
        outfile.write("PROJECT FOLDER TREE\n")
        outfile.write("="*50 + "\n")
        outfile.write("Project Root/\n")
        
        tree = generate_tree(".")
        for line in tree:
            outfile.write(line + "\n")
        
        outfile.write("\n\n")
        
        # --- WRITE COMPILED FILES ---
        for file_path in target:
            if os.path.exists(file_path):
                print(f"Adding: {file_path}")
                
                outfile.write(f"{'='*50}\n")
                outfile.write(f"File: {file_path}\n")
                outfile.write(f"{'='*50}\n\n")
                
                try:
                    with open(file_path, 'r', encoding='utf-8') as infile:
                        outfile.write(infile.read())
                        outfile.write("\n\n")
                except Exception as e:
                    print(f"  -> Error reading {file_path}: {e}")
                    outfile.write(f"// Error reading file: {e}\n\n")
            else:
                print(f"Warning: Could not find {file_path}")
                outfile.write(f"{'='*50}\n")
                outfile.write(f"File: {file_path} (NOT FOUND)\n")
                outfile.write(f"{'='*50}\n\n")

    print(f"\nDone! Your code and tree are ready in {output_filename}")

if __name__ == "__main__":
    compile_project()