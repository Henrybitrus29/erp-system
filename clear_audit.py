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

    print("Clearing security audit tables...")

    cursor.execute("SET FOREIGN_KEY_CHECKS = 0;")
    
    # List of all potential audit/tamper log tables to wipe
    tables_to_clear = ["sales_tamper_log", "security_audit_logs", "audit_logs"]
    
    for table in tables_to_clear:
        try:
            cursor.execute(f"TRUNCATE TABLE {table};")
            print(f"   └─ Cleared table: {table}")
        except mysql.connector.Error as err:
            if err.errno == 1146: # Table doesn't exist
                pass
            else:
                print(f"   └─ Couldn't truncate {table}: {err}")

    cursor.execute("SET FOREIGN_KEY_CHECKS = 1;")
    conn.commit()
    print("✅ All security audit logs have been completely wiped!")

except Exception as e:
    print(f"❌ Error clearing audit logs: {e}")

finally:
    if 'cursor' in locals():
        cursor.close()
    if 'conn' in locals() and conn.is_connected():
        conn.close()