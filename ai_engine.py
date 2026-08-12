import os
import mysql.connector
from flask import Flask, request, jsonify

app = Flask(__name__)

# ==========================================
# ROUTE 1: AI INVENTORY ANALYSIS (Original)
# ==========================================
@app.route('/analyze', methods=['POST'])
def analyze():
    data = request.json
    product_name = data.get('product_name')
    current_stock = int(data.get('current_stock', 0))
    daily_sales = float(data.get('daily_sales', 0))
    lead_time_days = 5 
    
    days_remaining = current_stock / daily_sales if daily_sales > 0 else 999
    
    # 1. CRITICAL: Out of stock soon, or absolutely low stock
    if days_remaining <= lead_time_days or current_stock <= 5:
        suggestion = f"[CRITICAL] Reorder {product_name} Immediately"
        if current_stock <= 5 and daily_sales == 0:
            explanation = f"Absolute Low Stock Alert: You only have {current_stock} units left. Even with no recent sales, this is below the minimum safe threshold."
        else:
            explanation = f"Velocity Alert: Average daily sales are {round(daily_sales, 1)} units. Current stock ({current_stock} units) will deplete in {round(days_remaining)} days."
        return jsonify({"status": "alert", "suggestion": suggestion, "explanation": explanation})
        
    # 2. PROFIT OPPORTUNITY: High sales velocity and healthy stock
    elif daily_sales >= 2.0 and current_stock > 10:
        suggestion = f"[PROFIT] Capitalize on {product_name} Demand"
        explanation = f"Revenue Opportunity: Sales velocity is high at {round(daily_sales, 1)} units per day. Suggest applying a 5% price increase to maximize profit margins while demand surges."
        return jsonify({"status": "alert", "suggestion": suggestion, "explanation": explanation})

    # 3. WARNING: Approaching minimum levels
    elif days_remaining <= (lead_time_days + 7) or current_stock <= 10:
        suggestion = f"[WARNING] Prepare to Order {product_name}"
        explanation = f"Stock is approaching minimum levels. You have {current_stock} units remaining. Prepare for restocking soon."
        return jsonify({"status": "alert", "suggestion": suggestion, "explanation": explanation})
        
    # 4. OPTIMAL: Ignore completely (No dashboard spam)
    else:
        return jsonify({"status": "ignore"})


# ==========================================
# ROUTE 2: MAKE.COM WEBHOOK (New Cloud Pipeline)
# ==========================================
@app.route('/api/webhook', methods=['POST'])
def restock_webhook():
    # 1. Security Check: Validate Make.com
    auth_header = request.headers.get('Authorization')
    if auth_header != 'Bearer CHIDAMA-TECH-KEY-2026':
        return jsonify({'status': 'error', 'message': 'Unauthorized Access'}), 401

    # 2. Extract Data from Make.com
    data = request.get_json()
    product_id = data.get('product_id')
    quantity = data.get('quantity')
    supplier = data.get('supplier', 'Telegram Automated Order')

    if not product_id or not quantity:
        return jsonify({'status': 'error', 'message': 'Missing product_id or quantity'}), 400

    try:
        # 3. Connect to your NEW API-Friendly Cloud Database
        # (Replace these with your actual cloud DB credentials once you migrate from InfinityFree)
        conn = mysql.connector.connect(
            host="YOUR_NEW_CLOUD_DB_HOST",
            user="YOUR_DB_USER",
            password="YOUR_DB_PASSWORD",
            database="erp_system"
        )
        cursor = conn.cursor(dictionary=True)

        # 4. Process the Restock
        cursor.execute("SELECT product_name, cost_price FROM products WHERE product_id = %s", (product_id,))
        product = cursor.fetchone()

        if not product:
            return jsonify({'status': 'error', 'message': 'Product not found'}), 404

        unit_price = product['cost_price']
        total_cost = unit_price * int(quantity)

        # 5. Update Database Tables
        cursor.execute(
            "INSERT INTO procurement (supplier, product_id, quantity, unit_price, total, status) VALUES (%s, %s, %s, %s, %s, 'Completed')",
            (supplier, product_id, quantity, unit_price, total_cost)
        )
        
        cursor.execute(
            "UPDATE products SET quantity = quantity + %s WHERE product_id = %s",
            (quantity, product_id)
        )

        conn.commit()
        cursor.close()
        conn.close()

        # 6. Return Success to Make.com
        return jsonify({
            'status': 'success',
            'message': f'Successfully restocked {quantity} units of {product["product_name"]}'
        }), 200

    except Exception as e:
        return jsonify({'status': 'error', 'message': str(e)}), 500


if __name__ == '__main__':
    # host='0.0.0.0' is required for Render to expose the server to the internet
    app.run(host='0.0.0.0', port=5000)