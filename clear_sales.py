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

    print("Clearing sales history and resetting counters...")

    # Disable foreign key checks temporarily to allow truncating
    cursor.execute("SET FOREIGN_KEY_CHECKS = 0;")
    
    # Wipe the sales table and reset sale_id auto-increment back to 1
    cursor.execute("TRUNCATE TABLE sales;")
    
    # Wipe the tamper log table if it exists
    cursor.execute("TRUNCATE TABLE sales_tamper_log;")
    
    # Re-enable foreign key checks
    cursor.execute("SET FOREIGN_KEY_CHECKS = 1;")

    conn.commit()
    print("✅ Sales table completely wiped! The system is now on a fresh slate starting at TXN-0001.")

except Exception as e:
    print(f"❌ Error during cleanup: {e}")

finally:
    if 'cursor' in locals():
        cursor.close()
    if 'conn' in locals() and conn.is_connected():
        conn.close()