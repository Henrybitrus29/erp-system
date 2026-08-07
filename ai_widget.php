<div class="card mt-4" style="background-color: #fff; border-radius: 12px; border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.02); margin-bottom: 25px;">
    
    <div class="card-header" style="background: #fff; border-bottom: 1px solid #f1f5f9; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; border-radius: 12px 12px 0 0;">
        <h5 style="margin: 0; font-weight: 700; font-size: 1.05rem; color: #1e293b; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-brain text-primary"></i> AI Inventory & Profit Analyst
        </h5>
        <a href="run_ai.php" class="btn btn-sm btn-light" style="font-size: 0.8rem; font-weight: 600; color: #4f46e5; border: 1px solid #e0e7ff; background: #eef2ff;">
            <i class="fa-solid fa-arrows-rotate me-1"></i> Refresh AI
        </a>
    </div>
    
    <div class="card-body" style="padding: 20px; background-color: #f8fafc;">
        <?php
        if(!isset($pdo)){ require_once 'db.php'; }
        
        try {
            $ai_stmt = $pdo->query("SELECT * FROM ai_insights ORDER BY 
                CASE 
                    WHEN suggestion LIKE '%[CRITICAL]%' THEN 1 
                    WHEN suggestion LIKE '%[PROFIT]%' THEN 2 
                    WHEN suggestion LIKE '%[WARNING]%' THEN 3 
                    ELSE 4 
                END, 
                created_at DESC LIMIT 6");
            $insights = $ai_stmt->fetchAll();

            if (count($insights) > 0) {
                echo "<div class='row g-3'>";
                
                foreach ($insights as $insight) {
                    $is_critical = strpos($insight['suggestion'], '[CRITICAL]') !== false;
                    $is_profit = strpos($insight['suggestion'], '[PROFIT]') !== false;
                    $is_warning = strpos($insight['suggestion'], '[WARNING]') !== false;
                    
                    $clean_suggestion = str_replace(['[CRITICAL]', '[WARNING]', '[PROFIT]', '[OPTIMAL]'], '', $insight['suggestion']);
                    
                    // Dynamic styling engine
                    $borderColor = $is_critical ? '#ef4444' : ($is_profit ? '#3b82f6' : '#f59e0b');
                    $icon = $is_critical ? '<i class="fa-solid fa-triangle-exclamation text-danger"></i>' : ($is_profit ? '<i class="fa-solid fa-money-bill-trend-up text-primary"></i>' : '<i class="fa-solid fa-circle-exclamation text-warning"></i>');
                    $badgeText = $is_critical ? 'URGENT' : ($is_profit ? 'OPPORTUNITY' : 'PREPARE');
                    $badgeColor = $is_critical ? 'bg-danger' : ($is_profit ? 'bg-primary' : 'bg-warning text-dark');

                    echo "
                    <div class='col-md-6 col-xl-4'>
                        <div style='background: white; border-top: 3px solid {$borderColor}; border-radius: 8px; padding: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.03); height: 100%; display: flex; flex-direction: column; justify-content: space-between;'>
                            
                            <div>
                                <div class='d-flex justify-content-between align-items-start mb-2'>
                                    <h6 style='margin: 0; font-size: 0.95rem; font-weight: 700; color: #1e293b; display: flex; align-items: flex-start; gap: 6px; line-height: 1.3;'>
                                        <span style='margin-top: 2px;'>{$icon}</span> 
                                        <span>{$clean_suggestion}</span>
                                    </h6>
                                    <span class='badge {$badgeColor}' style='font-size: 0.65rem; padding: 4px 6px;'>{$badgeText}</span>
                                </div>
                                
                                <p style='margin: 0 0 15px 0; color: #64748b; font-size: 0.8rem; line-height: 1.4;'>
                                    " . htmlspecialchars($insight['explanation']) . "
                                </p>
                            </div>";

                    // The Admin Action Area
                    if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') {
                        echo "<div style='margin-top: auto; padding-top: 12px; border-top: 1px dashed #e2e8f0;'>";
                        
                        // Action for Restocks
                        if ($is_critical || $is_warning) {
                            echo "
                                <form action='actions.php' method='POST' class='input-group input-group-sm'>
                                    <input type='hidden' name='action' value='ai_instant_restock'>
                                    <input type='hidden' name='product_id' value='{$insight['product_id']}'>
                                    <input type='number' name='quantity' class='form-control' style='border-color: #cbd5e1; font-size: 0.8rem;' placeholder='Qty...' required min='1'>
                                    <button type='submit' class='btn' style='background-color: {$borderColor}; color: white; font-weight: 600; font-size: 0.8rem; padding: 4px 12px; z-index: 0;'>
                                        <i class='fa-solid fa-cart-plus'></i> Approve
                                    </button>
                                </form>
                            ";
                        }
                        // Action for Profit Optimization
                        elseif ($is_profit) {
                            echo "
                                <form action='actions.php' method='POST'>
                                    <input type='hidden' name='action' value='ai_profit_increase'>
                                    <input type='hidden' name='product_id' value='{$insight['product_id']}'>
                                    <button type='submit' class='btn w-100' style='background-color: {$borderColor}; color: white; font-weight: 600; font-size: 0.8rem; padding: 6px 12px;'>
                                        <i class='fa-solid fa-arrow-trend-up'></i> Apply 5% Price Increase
                                    </button>
                                </form>
                            ";
                        }
                        
                        echo "</div>";
                    }

                    echo "
                        </div>
                    </div>";
                }
                echo "</div>";
            } else {
                echo "<div class='text-center py-4 text-muted'><i class='fa-solid fa-box-open mb-2' style='font-size: 2rem;'></i><br><small>Inventory and pricing are fully optimized.</small></div>";
            }
        } catch (Exception $e) {
            echo "<p class='text-danger small'>Database error: Please ensure the AI table is set up correctly.</p>";
        }
        ?>
    </div>
</div>