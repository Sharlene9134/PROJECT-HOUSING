<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Add Property | House System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= base_url('assets/app.css') ?>" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="app-dark">

<nav class="navbar navbar-expand-lg navbar-dark app-navbar">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= base_url('/admin/dashboard') ?>">🏠 House Admin</a>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-light btn-sm" href="<?= base_url('/admin/dashboard') ?>">Dashboard</a>
      <a class="btn btn-outline-light btn-sm" href="<?= base_url('/admin/users') ?>">Users</a>
      <a class="btn btn-outline-light btn-sm" href="<?= base_url('/admin/properties') ?>">Properties</a>
      <a class="btn btn-outline-light btn-sm" href="<?= base_url('/admin/offers') ?>">Offers</a>
      <a class="btn btn-outline-light btn-sm" href="<?= base_url('/admin/payments') ?>">Payments</a>
      <a class="btn btn-danger btn-sm" href="<?= base_url('/logout') ?>">Logout</a>
    </div>
  </div>
</nav>

<div class="container my-4">
  <div class="d-flex align-items-end justify-content-between mb-3">
    <div>
      <div class="section-title text-white fs-4 mb-0">
        <i class="bi bi-plus-square me-2"></i>Add Property
      </div>
      <div class="text-muted small">Create a new listing (title, price, location, and image).</div>
    </div>
    <a href="<?= base_url('/admin/properties') ?>" class="btn btn-outline-light btn-sm">
      <i class="bi bi-arrow-left me-1"></i>Back
    </a>
  </div>

  <?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
  <?php endif; ?>
  <?php if(session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
  <?php endif; ?>

  <?php if(isset($errors)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach($errors as $field => $error): ?>
          <li><?= esc($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" action="<?= base_url('/admin/add_property') ?>" enctype="multipart/form-data" class="row g-3">
    <?= csrf_field() ?>

    <div class="col-md-6">
      <label for="seller_id" class="form-label">Seller</label>
      <select id="seller_id" name="seller_id" class="form-select" required>
        <option value="">-- Select Seller --</option>
        <?php foreach(($sellers ?? []) as $s): ?>
          <?php $selected = (string)old('seller_id', $seller_id ?? '') === (string)$s['id'] ? 'selected' : ''; ?>
          <option value="<?= esc($s['id']) ?>" <?= $selected ?>><?= esc($s['name'] ?? '') ?> (ID: <?= esc($s['id']) ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-6">
      <label for="price" class="form-label">Price</label>
      <input type="number" step="0.01" id="price" name="price" class="form-control" value="<?= esc(old('price', $price ?? '')) ?>" required>
    </div>

    <div class="col-md-12">
      <label for="title" class="form-label">Property Title</label>
      <input type="text" id="title" name="title" class="form-control" value="<?= esc(old('title', $title ?? '')) ?>" required>
    </div>

    <div class="col-md-12">
      <label for="location" class="form-label">Location</label>
      <input type="text" id="location" name="location" class="form-control" value="<?= esc(old('location', $location ?? '')) ?>" required>
    </div>

    <div class="col-md-12">
      <label for="description" class="form-label">Description</label>
      <textarea id="description" name="description" class="form-control" rows="4" required><?= esc(old('description', $description ?? '')) ?></textarea>
    </div>

    <div class="col-md-12">
      <label for="image" class="form-label">Property Image</label>
      <input type="file" id="image" name="image" class="form-control" accept="image/*">
      <div class="form-text text-muted">Optional. Leave empty to create without an image.</div>
    </div>

    <div class="col-12 text-end">
      <button type="submit" class="btn btn-success">Add Property</button>
    </div>
  </form>
</div>

<!-- Add Socket.io Client Library -->
<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>

<script>
// Socket.io connection
const socket = io('http://localhost:3000', {
    transports: ['websocket', 'polling']
});

// Connection status
let isConnected = false;

// When connected
socket.on('connect', () => {
    console.log('✅ Connected to Socket.io server');
    isConnected = true;
    showNotification('Real-time connection established', 'success');
    updateConnectionStatus(true);
});

// When disconnected
socket.on('disconnect', () => {
    console.log('❌ Disconnected from Socket.io server');
    isConnected = false;
    showNotification('Real-time connection lost', 'warning');
    updateConnectionStatus(false);
});

// Listen for new properties from other admins
socket.on('property-added', (property) => {
    console.log('New property received:', property);
    showNotification(
        `🏠 New property "${property.title}" added! Price: $${property.price} | Location: ${property.location}`,
        'info'
    );
    
    // Optional: Play sound
    // playNotificationSound();
    
    // Optional: Update counter on page
    updatePropertyCounter();
});

// Function to send property data via Socket.io
function broadcastNewProperty(propertyData) {
    if (isConnected) {
        // Send POST request to your /new-property endpoint
        fetch('http://localhost:3000/new-property', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(propertyData)
        })
        .then(response => response.json())
        .then(data => {
            console.log('Property broadcasted:', data);
            showNotification('Property notification sent to all admins!', 'success');
        })
        .catch(error => {
            console.error('Error broadcasting property:', error);
            showNotification('Failed to send real-time notification', 'warning');
        });
    } else {
        console.warn('Socket.io not connected');
        showNotification('Cannot send notification (offline)', 'warning');
    }
}

// UI Functions
function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show`;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.style.animation = 'slideIn 0.3s ease-out';
    notification.innerHTML = `
        <i class="bi ${type === 'success' ? 'bi-check-circle' : type === 'info' ? 'bi-info-circle' : 'bi-exclamation-triangle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

function updateConnectionStatus(connected) {
    let statusBadge = document.getElementById('socketStatus');
    if (!statusBadge) {
        statusBadge = document.createElement('div');
        statusBadge.id = 'socketStatus';
        statusBadge.className = 'badge mb-3';
        statusBadge.style.position = 'fixed';
        statusBadge.style.top = '80px';
        statusBadge.style.right = '20px';
        statusBadge.style.zIndex = '9999';
        statusBadge.style.padding = '8px 12px';
        document.body.appendChild(statusBadge);
    }
    
    if (connected) {
        statusBadge.className = 'badge bg-success mb-3';
        statusBadge.innerHTML = '<i class="bi bi-wifi"></i> Live: Connected';
    } else {
        statusBadge.className = 'badge bg-danger mb-3';
        statusBadge.innerHTML = '<i class="bi bi-wifi-off"></i> Live: Disconnected';
    }
}

function updatePropertyCounter() {
    // Optional: Update any property counter on the page
    const counterElements = document.querySelectorAll('.property-count');
    counterElements.forEach(el => {
        let current = parseInt(el.textContent) || 0;
        el.textContent = current + 1;
    });
}

// Play notification sound (optional)
function playNotificationSound() {
    try {
        const audio = new Audio('data:audio/wav;base64,U3RlYWx0aCBzb3VuZA==');
        audio.volume = 0.3;
        audio.play().catch(e => console.log('Audio play failed:', e));
    } catch(e) {
        console.log('Sound not supported');
    }
}

// INTERCEPT FORM SUBMISSION
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('propertyForm');
    
    if (form) {
        // Don't prevent default submission, just add WebSocket broadcast
        form.addEventListener('submit', function(e) {
            // Get form data
            const sellerSelect = document.getElementById('seller_id');
            const sellerText = sellerSelect.options[sellerSelect.selectedIndex]?.text || '';
            
            const propertyData = {
                title: document.getElementById('title')?.value || '',
                price: document.getElementById('price')?.value || '',
                location: document.getElementById('location')?.value || '',
                description: document.getElementById('description')?.value || '',
                seller_id: document.getElementById('seller_id')?.value || '',
                seller_name: sellerText,
                added_by: '<?= session()->get('user_name') ?? 'Admin' ?>',
                added_by_id: '<?= session()->get('user_id') ?? 'unknown' ?>',
                timestamp: new Date().toISOString()
            };
            
            // Broadcast via Socket.io (this runs BEFORE form submits)
            broadcastNewProperty(propertyData);
            
            // Show sending indicator
            showNotification('Sending property notification...', 'info');
            
            // Form will continue normal submission to PHP
            // The page will reload and show success/error messages
        });
    }
});

// Add CSS animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;
document.head.appendChild(style);

// Optional: Test connection on page load
console.log('Socket.io client loaded, connecting to server...');
</script>

</body>
</html>

