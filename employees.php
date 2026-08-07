<?php 
require 'db.php'; 
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: dashboard.php"); 
    exit; 
}
$pageTitle = 'Employees';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees - ERP SYSTEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7fe; color: #1e293b; overflow-x: hidden; }
        .main-content { margin-left: 260px; padding: 30px; min-height: 100vh; }
        .ui-card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); border: 1px solid #f1f5f9; }
        .card-header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .btn-add-primary { background: #6366f1; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; transition: 0.2s; }
        .table-custom { width: 100%; border-collapse: collapse; }
        .table-custom th { color: #64748b; font-size: 0.85rem; font-weight: 600; padding: 15px 10px; border-bottom: 1px solid #e2e8f0; }
        .table-custom td { padding: 15px 10px; font-size: 0.9rem; color: #1e293b; border-bottom: 1px solid #f1f5f9; vertical-align: middle; font-weight: 500; }
        .badge-status { padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; display: inline-block; background: #dcfce7; color: #10b981; }
        .btn-act { width: 32px; height: 32px; border-radius: 6px; border: none; display: flex; align-items: center; justify-content: center; color: white; transition: 0.2s; }
        .btn-edit { background: #3b82f6; } .btn-edit:hover { background: #2563eb; }
        .btn-del { background: #ef4444; } .btn-del:hover { background: #dc2626; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <?php include 'header.php'; ?>
        
        <?php if (isset($_GET['msg'])): ?><div class="alert alert-success rounded-3 mb-3"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
        <?php if (isset($_GET['error'])): ?><div class="alert alert-danger rounded-3 mb-3"><?= htmlspecialchars($_GET['error']) ?></div><?php endif; ?>

        <div class="ui-card">
            <div class="card-header-flex">
                <h5 class="m-0 fw-bold text-dark" style="visibility: hidden;">Directory</h5>
                <button class="btn-add-primary" data-bs-toggle="modal" data-bs-target="#addEmpModal"><i class="fa-solid fa-plus me-1"></i> Add Employee</button>
            </div>
            <div class="table-responsive">
                <table class="table-custom">
                    <thead><tr><th>ID</th><th>Full Name</th><th>Department</th><th>Phone</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                    <tbody>
                        <?php
                        $employees = $pdo->query("SELECT * FROM employees ORDER BY employee_id ASC")->fetchAll();
                        if (count($employees) > 0) {
                            foreach ($employees as $emp) {
                                $formatted_id = 'EMP' . str_pad($emp['employee_id'], 3, '0', STR_PAD_LEFT);
                                echo "<tr>
                                        <td class='text-muted'>{$formatted_id}</td>
                                        <td class='fw-bold'>".htmlspecialchars($emp['name'])."</td>
                                        <td>".htmlspecialchars($emp['department'])."</td>
                                        <td>".htmlspecialchars($emp['phone_number'])."</td>
                                        <td><span class='badge-status'>Active</span></td>
                                        <td class='text-end'>
                                            <div class='d-flex justify-content-end gap-2'>
                                                <button class='btn-act btn-edit' data-bs-toggle='modal' data-bs-target='#editModal{$emp['employee_id']}'><i class='fa-solid fa-pen'></i></button>
                                                <form action='actions.php' method='POST' class='d-inline' onsubmit=\"return confirm('Are you sure you want to delete this employee?');\">
                                                    <input type='hidden' name='action' value='delete_employee'>
                                                    <input type='hidden' name='employee_id' value='{$emp['employee_id']}'>
                                                    <button type='submit' class='btn-act btn-del'><i class='fa-solid fa-trash'></i></button>
                                                </form>
                                            </div>
                                        </td>
                                      </tr>";

                                // Individual Edit Modal for each employee
                                echo '<div class="modal fade" id="editModal'.$emp['employee_id'].'" tabindex="-1" aria-hidden="true">
                                  <div class="modal-dialog">
                                    <div class="modal-content border-0 shadow text-start">
                                      <form action="actions.php" method="POST">
                                          <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-bold">Edit Employee</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                          </div>
                                          <div class="modal-body">
                                              <input type="hidden" name="action" value="edit_employee">
                                              <input type="hidden" name="employee_id" value="'.$emp['employee_id'].'">
                                              <div class="mb-3"><label class="form-label small fw-semibold text-muted">Full Name</label><input type="text" name="name" class="form-control" value="'.htmlspecialchars($emp['name']).'" required></div>
                                              <div class="mb-3"><label class="form-label small fw-semibold text-muted">Gender</label><select name="gender" class="form-select" required><option value="Male" '.($emp['gender'] == 'Male' ? 'selected' : '').'>Male</option><option value="Female" '.($emp['gender'] == 'Female' ? 'selected' : '').'>Female</option></select></div>
                                              <div class="mb-3"><label class="form-label small fw-semibold text-muted">Department</label><input type="text" name="department" class="form-control" value="'.htmlspecialchars($emp['department']).'" required></div>
                                              <div class="mb-3"><label class="form-label small fw-semibold text-muted">Phone Number</label><input type="text" name="phone" class="form-control" value="'.htmlspecialchars($emp['phone_number']).'" required></div>
                                          </div>
                                          <div class="modal-footer border-0 pt-0">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary" style="background:#6366f1; border:none;">Save Changes</button>
                                          </div>
                                      </form>
                                    </div>
                                  </div>
                                </div>';
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center text-muted py-4'>No employees found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Add Employee Modal -->
    <div class="modal fade" id="addEmpModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <form action="actions.php" method="POST">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">Register Employee</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_employee">
                        <div class="mb-3"><label class="form-label small fw-semibold text-muted">Full Name</label><input type="text" name="name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label small fw-semibold text-muted">Gender</label><select name="gender" class="form-select" required><option value="">Select...</option><option value="Male">Male</option><option value="Female">Female</option></select></div>
                        <div class="mb-3"><label class="form-label small fw-semibold text-muted">Department</label><input type="text" name="department" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label small fw-semibold text-muted">Phone</label><input type="text" name="phone" class="form-control" required></div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="background:#6366f1; border:none;">Add Staff</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
