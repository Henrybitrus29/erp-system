from flask import Flask, request, jsonify

app = Flask(__name__)

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

if __name__ == '__main__':
    app.run(port=5000)