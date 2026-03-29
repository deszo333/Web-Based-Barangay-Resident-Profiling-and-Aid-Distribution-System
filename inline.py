import os
import re

def find_inline_css(directory="."):
    # Regex patterns to catch style attributes and <style> tags
    style_attr_re = re.compile(r'style\s*=\s*["\']([^"\']+)["\']', re.IGNORECASE)
    style_tag_re = re.compile(r'<style\b[^>]*>', re.IGNORECASE)

    found_files = {}
    total_issues = 0

    print(f"🔍 Scanning directory: {os.path.abspath(directory)} for inline CSS...\n")

    # Walk through all folders and files
    for root, dirs, files in os.walk(directory):
        for file in files:
            if file.endswith(".php"):
                filepath = os.path.join(root, file)
                
                try:
                    with open(filepath, 'r', encoding='utf-8') as f:
                        lines = f.readlines()
                        
                    file_issues = []
                    for i, line in enumerate(lines):
                        # 1. Check for style="..." attributes
                        attr_matches = style_attr_re.findall(line)
                        if attr_matches:
                            for match in attr_matches:
                                file_issues.append((i + 1, f'style="{match}"'))
                                total_issues += 1
                        
                        # 2. Check for <style> tags
                        if style_tag_re.search(line):
                            file_issues.append((i + 1, '<style> tag found'))
                            total_issues += 1
                    
                    if file_issues:
                        found_files[filepath] = file_issues
                        
                except Exception as e:
                    print(f"⚠️ Error reading {filepath}: {e}")
    
    # Print the final report
    if not found_files:
        print("✅ Clean! No inline CSS found in any PHP files.")
    else:
        print(f"🚨 Found {total_issues} inline CSS instances across {len(found_files)} files:\n")
        print("=" * 60)
        
        for filepath, issues in found_files.items():
            print(f"📄 {filepath}")
            for line_num, snippet in issues:
                # Truncate super long CSS strings for the terminal output
                short_snippet = snippet if len(snippet) < 60 else snippet[:57] + "..."
                print(f"   Ln {line_num:<4} | {short_snippet}")
            print("-" * 60)

if __name__ == "__main__":
    # Runs the scan in the current folder. 
    # Change "." to your folder path if running from outside the project.
    find_inline_css(".")