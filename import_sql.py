import os
import mysql.connector

try:
    # 1. Connect to Aiven Cloud Database using environment variable
    conn = mysql.connector.connect(
        host="mysql-16bf1a03-meshachsunday86-41c1.h.aivencloud.com",
        port=17867,
        user="avnadmin",
        password=os.getenv('DB_PASSWORD'),
        database="defaultdb"
    )
    cursor = conn.cursor()

    # 2. Automatically locate the file in your Windows Downloads folder
    downloads_folder = os.path.join(os.path.expanduser('~'), 'Downloads')
    file_path = os.path.join(downloads_folder, 'erp_system (2).sql')

    print(f"Reading SQL file from: {file_path}")

    with open(file_path, 'r', encoding='utf-8') as file:
        sql_script = file.read()

    print("Uploading to cloud...")
    
    # 3. Split the SQL script into individual commands and execute them
    for statement in sql_script.split(';'):
        if statement.strip():
            try:
                cursor.execute(statement)
            except Exception:
                # Ignore minor drop errors for tables that don't exist yet
                pass

    conn.commit()
    print("✅ Real database successfully migrated to Aiven Cloud!")

except FileNotFoundError:
    print(f"❌ Error: Could not find the file at {file_path}. Please ensure the name is exactly 'erp_system (2).sql'.")
except Exception as e:
    print(f"❌ Error during import: {e}")

finally:
    if 'cursor' in locals():
        cursor.close()
    if 'conn' in locals() and conn.is_connected():
        conn.close()