<?php
session_start();
require 'config.php';
require 'includes/csrf.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle friend operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            $friend_id = (int) ($_POST['friend_id'] ?? 0);
            $friend_name = trim($_POST['friend_name'] ?? '');

            if ($friend_id <= 0 || empty($friend_name)) {
                $error = 'Please fill in all fields.';
            } elseif ($friend_id == $user_id) {
                $error = 'You cannot add yourself as a friend.';
            } else {
                try {
                    // Check if friend exists
                    $stmt = $pdo->prepare("SELECT user_id FROM Users WHERE user_id = ?");
                    $stmt->execute([$friend_id]);
                    if (!$stmt->fetch()) {
                        throw new Exception('User not found.');
                    }

                    // Check if already friends
                    $stmt = $pdo->prepare("SELECT * FROM friends_of WHERE user_id = ? AND friend_id = ?");
                    $stmt->execute([$user_id, $friend_id]);
                    if ($stmt->fetch()) {
                        throw new Exception('Already friends with this user.');
                    }

                    $stmt = $pdo->prepare("INSERT INTO friends_of (user_id, friend_id, friend_name) VALUES (?, ?, ?)");
                    $stmt->execute([$user_id, $friend_id, $friend_name]);
                    $message = 'Friend added successfully!';
                } catch (PDOException $e) {
                    $error = 'Failed to add friend.';
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
        } elseif ($action === 'remove') {
            $friend_id = (int) ($_POST['friend_id'] ?? 0);
            if ($friend_id > 0) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM friends_of WHERE user_id = ? AND friend_id = ?");
                    $stmt->execute([$user_id, $friend_id]);
                    $message = 'Friend removed successfully!';
                } catch (PDOException $e) {
                    $error = 'Failed to remove friend.';
                }
            }
        }
    }
}


// Get user's friends
$stmt = $pdo->prepare("
    SELECT f.*, u.full_name, u.workplace, r.region_name
    FROM friends_of f
    JOIN Users u ON f.friend_id = u.user_id
    LEFT JOIN Region r ON u.region_id = r.region_id
    WHERE f.user_id = ?
    ORDER BY f.friend_name
");
$stmt->execute([$user_id]);
$friends = $stmt->fetchAll();

// Get all users (for adding friends)
$stmt = $pdo->prepare("
    SELECT user_id, full_name, workplace
    FROM Users
    WHERE user_id != ?
    AND user_id NOT IN (SELECT friend_id FROM friends_of WHERE user_id = ?)
    ORDER BY full_name
");
$stmt->execute([$user_id, $user_id]);
$available_users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends - Stock Trading</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body class="dashboard-page">
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="logo">
                <span class="logo-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 18L7 12L11 15L15 8L19 11L21 9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="7" cy="12" r="1.5" fill="currentColor"/>
                        <circle cx="11" cy="15" r="1.5" fill="currentColor"/>
                        <circle cx="15" cy="8" r="1.5" fill="currentColor"/>
                        <circle cx="19" cy="11" r="1.5" fill="currentColor"/>
                    </svg>
                </span>
                <span class="logo-text">StockTrader</span>
            </h1>
            <ul class="nav-menu">
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="stocks.php">Stocks</a></li>
                <li><a href="buy_sell.php">Trade</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="portfolio.php">Portfolio</a></li>
                <li><a href="history.php">History</a></li>
                <li><a href="watchlist.php">Watchlist</a></li>
                <li><a href="alerts.php">Alerts</a></li>
                <li><a href="analytics.php">Analytics</a></li>
                <li><a href="account.php">Account</a></li>
                <li><a href="friends.php" class="active">Friends</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>My Friends</h2>
            <button class="btn btn-primary" onclick="openModal()">+ Add Friend</button>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="content-grid">
            <div class="card">
                <h3>Your Friends (<?php echo count($friends); ?>)</h3>
                <div class="friends-list">
                    <?php if (empty($friends)): ?>
                        <div class="empty-state">No friends yet. Add some friends to connect!</div>
                    <?php else: ?>
                        <?php foreach ($friends as $friend): ?>
                            <div class="friend-card">
                                <div class="friend-info">
                                    <h4><?php echo htmlspecialchars($friend['friend_name']); ?></h4>
                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($friend['full_name']); ?></p>
                                    <p><strong>Workplace:</strong>
                                        <?php echo htmlspecialchars($friend['workplace'] ?? 'N/A'); ?></p>
                                    <p><strong>Region:</strong> <?php echo htmlspecialchars($friend['region_name'] ?? 'N/A'); ?>
                                    </p>
                                </div>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Remove this friend?');">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="friend_id" value="<?php echo $friend['friend_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Add Friend</h3>
            <form method="POST" action="friends.php">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="add">

                <div class="form-group">
                    <label for="friend_id">Select User</label>
                    <select id="friend_id" name="friend_id" required onchange="updateFriendName()">
                        <option value="">Select a user</option>
                        <?php foreach ($available_users as $user): ?>
                            <option value="<?php echo $user['user_id']; ?>"
                                data-name="<?php echo htmlspecialchars($user['full_name']); ?>">
                                <?php echo htmlspecialchars($user['full_name']); ?>
                                <?php if ($user['workplace']): ?>
                                    (<?php echo htmlspecialchars($user['workplace']); ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="friend_name">Friend Name (Nickname)</label>
                    <input type="text" id="friend_name" name="friend_name" required
                        placeholder="Enter a nickname for this friend">
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Friend</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('modal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('modal').style.display = 'none';
        }

        function updateFriendName() {
            const select = document.getElementById('friend_id');
            const nameInput = document.getElementById('friend_name');
            const selectedOption = select.options[select.selectedIndex];
            if (selectedOption.value) {
                nameInput.value = selectedOption.getAttribute('data-name');
            }
        }

        window.onclick = function (event) {
            const modal = document.getElementById('modal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>

</html>