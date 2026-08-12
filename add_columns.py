import os
import mysql.connector

try:
    conn = mysql.connector.connect(
        host="mysql-16bf1a03-meshachsunday86-41c1.h.aivencloud.com",
        port=17867,
        user="avnadmin",
        password=os.getenv('DB_PASSWORD'),
        database="defaultdb"
    )
    cursor = conn.cursor()

    print("Adding security columns to sales table...")
    
    # Standard MySQL syntax without IF NOT EXISTS
    cursor.execute("ALTER TABLE sales ADD COLUMN previous_hash VARCHAR(255);")
    cursor.execute("ALTER TABLE sales ADD COLUMN hash_signature VARCHAR(255);")

    conn.commit()
    print("✅ Security audit hash columns successfully added to the cloud database!")

except mysql.connector.Error as err:
    if err.errno == 1060: # Error 1060 = Duplicate column name (columns already exist)
        print("ℹ️ Columns already exist in the table, moving forward!")
    else:
        print(f"❌ Error: {err}")

finally:
    if 'cursor' in locals():
        cursor.close()
    if 'conn' in locals() and conn.is_connected():
        conn.close()