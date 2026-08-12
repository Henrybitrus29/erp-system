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

    print("All columns in the cloud 'sales' table:")
    cursor.execute("SHOW COLUMNS FROM sales")
    for row in cursor.fetchall():
        print(f"- {row[0]}")

except Exception as e:
    print(f"❌ Error checking columns: {e}")

finally:
    if 'cursor' in locals():
        cursor.close()
    if 'conn' in locals() and conn.is_connected():
        conn.close()