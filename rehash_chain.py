import os
import hashlib
import mysql.connector

try:
    conn = mysql.connector.connect(
        host="mysql-16bf1a03-meshachsunday86-41c1.h.aivencloud.com",
        port=17867,
        user="avnadmin",
        password=os.getenv('DB_PASSWORD'),
        database="defaultdb"
    )
    cursor = conn.cursor(dictionary=True)

    print("Rebuilding cryptographic hash chain...")
    
    # Fetch all sales in chronological order
    cursor.execute("SELECT * FROM sales ORDER BY sale_id ASC")
    rows = cursor.fetchall()
    
    previous_hash = "GENESIS_BLOCK"
    
    for row in rows:
        sale_id = row['sale_id']
        product_id = row['product_id']
        quantity = row['quantity']
        total_price = row['total_price']
        
        # Format transaction string exactly as the PHP frontend checks it:
        # Format: PREV_HASH|sale_id|product_id|quantity|total_price
        payload = f"{previous_hash}|{sale_id}|{product_id}|{quantity}|{total_price}"
        current_hash = hashlib.sha256(payload.encode('utf-8')).hexdigest()
        
        # Update row with recalculations
        update_cursor = conn.cursor()
        update_cursor.execute(
            "UPDATE sales SET previous_hash = %s, hash_signature = %s WHERE sale_id = %s",
            (previous_hash, current_hash, sale_id)
        )
        update_cursor.close()
        
        # Pass hash to the next transaction in chain
        previous_hash = current_hash

    conn.commit()
    print("✅ Blockchain hash signatures successfully rebuilt!")

except Exception as e:
    print(f"❌ Error during hashing: {e}")

finally:
    if 'cursor' in locals():
        cursor.close()
    if 'conn' in locals() and conn.is_connected():
        conn.close()