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

    # 1. Fix tampered quantities
    cursor.execute("UPDATE sales SET quantity = 2 WHERE sale_id = 1")
    cursor.execute("UPDATE sales SET quantity = 10 WHERE sale_id = 18")

    # 2. Recalculate ALL totals using 'quantity' and 'cost_price'
    sync_query = """
    UPDATE sales s
    JOIN products p ON s.product_id = p.product_id
    SET s.total_price = s.quantity * p.cost_price;
    """
    cursor.execute(sync_query)

    conn.commit()
    print("✅ All quantities and totals have been mathematically restored to normal!")

except Exception as e:
    print(f"❌ Error during restoration: {e}")

finally:
    if 'cursor' in locals():
        cursor.close()
    if 'conn' in locals() and conn.is_connected():
        conn.close()