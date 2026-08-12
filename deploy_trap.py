import os
import mysql.connector

try:
    # Connect to your Live Aiven Cloud Database
    conn = mysql.connector.connect(
        host="mysql-16bf1a03-meshachsunday86-41c1.h.aivencloud.com",
        port=17867,
        user="avnadmin",
        password=os.getenv('DB_PASSWORD'),
        database="defaultdb"
    )

    cursor = conn.cursor()

    # 1. Ensure the main sales table exists
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS sales (
        sale_id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    """)

    # 2. Create the hidden tamper log table
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS sales_tamper_log (
        log_id INT AUTO_INCREMENT PRIMARY KEY,
        sale_id INT NOT NULL,
        old_total DECIMAL(10,2),
        new_total DECIMAL(10,2),
        action_type VARCHAR(50),
        tampered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    """)

    # 3. Create the UPDATE Trigger (Catches altered amounts)
    cursor.execute("DROP TRIGGER IF EXISTS detect_sales_update;")
    cursor.execute("""
    CREATE TRIGGER detect_sales_update
    AFTER UPDATE ON sales
    FOR EACH ROW
    BEGIN
        -- Only trigger the alarm if the financial amount is changed
        IF OLD.total_price != NEW.total_price THEN
            INSERT INTO sales_tamper_log (sale_id, old_total, new_total, action_type)
            VALUES (OLD.sale_id, OLD.total_price, NEW.total_price, 'ALTERED_SALE');
        END IF;
    END;
    """)

    # 4. Create the DELETE Trigger (Catches deleted records)
    cursor.execute("DROP TRIGGER IF EXISTS detect_sales_delete;")
    cursor.execute("""
    CREATE TRIGGER detect_sales_delete
    BEFORE DELETE ON sales
    FOR EACH ROW
    BEGIN
        -- Log the exact amount that was wiped out
        INSERT INTO sales_tamper_log (sale_id, old_total, new_total, action_type)
        VALUES (OLD.sale_id, OLD.total_price, 0.00, 'DELETED_SALE');
    END;
    """)

    conn.commit()
    print("🚨 Database security triggers and hidden logs deployed successfully!")

except Exception as e:
    print(f"❌ Error deploying traps: {e}")

finally:
    if 'cursor' in locals():
        cursor.close()
    if 'conn' in locals() and conn.is_connected():
        conn.close()