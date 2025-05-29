<?php
$page_title = 'Categories';
$current_page = 'categories';

require_once 'config/database.php';
require_once 'includes/functions.php';

$error = null;
$success = null;

// Handle form submission for new/edit category
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $id = $_POST['id'] ?? null;

    if (empty($name)) {
        $error = "Category name is required.";
    } else {
        try {
            if ($id) {
                // Update existing category
                $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
                $stmt->bind_param("ssi", $name, $description, $id);
            } else {
                // Insert new category
                $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                $stmt->bind_param("ss", $name, $description);
            }
            
            if ($stmt->execute()) {
                $success = "Category " . ($id ? "updated" : "created") . " successfully!";
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $error = "An error occurred while saving the category.";
        }
    }
}

// Get all categories
try {
    $result = $conn->query("SELECT * FROM categories ORDER BY name");
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $error = "An error occurred while fetching categories.";
}

// Start output buffering
ob_start();
?>

<!-- Categories Content -->
<div class="row g-4">
    <!-- Add/Edit Category Form -->
    <div class="col-md-4">
        <div class="dashboard-card">
            <h5 class="mb-4">Add New Category</h5>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="post" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label class="form-label">Category Name *</label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="3"></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Save Category
                </button>
            </form>
        </div>
    </div>

    <!-- Categories List -->
    <div class="col-md-8">
        <div class="dashboard-card">
            <h5 class="mb-4">Categories List</h5>
            
            <?php if (empty($categories)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                    <h5>No Categories Found</h5>
                    <p class="text-muted">Add your first category using the form.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th>Products</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                <td><?php echo htmlspecialchars($category['description']); ?></td>
                                <td>
                                    <?php
                                    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
                                    $stmt->bind_param("i", $category['id']);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $count = $result->fetch_assoc()['count'];
                                    echo $count;
                                    ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary edit-category" 
                                                data-id="<?php echo $category['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($category['name']); ?>"
                                                data-description="<?php echo htmlspecialchars($category['description']); ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($count == 0): ?>
                                        <a href="index.php?page=categories&delete=<?php echo $category['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to delete this category?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-category-id">
                    <div class="mb-3">
                        <label class="form-label">Category Name *</label>
                        <input type="text" class="form-control" name="name" id="edit-category-name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="edit-category-description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$page_scripts = <<<SCRIPTS
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit category button clicks
    document.querySelectorAll('.edit-category').forEach(button => {
        button.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
            document.getElementById('edit-category-id').value = this.dataset.id;
            document.getElementById('edit-category-name').value = this.dataset.name;
            document.getElementById('edit-category-description').value = this.dataset.description;
            modal.show();
        });
    });
});
</script>
SCRIPTS;

$page_content = ob_get_clean();
require_once 'templates/dashboard_template.php';
?> 