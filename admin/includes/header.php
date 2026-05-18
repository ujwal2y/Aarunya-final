<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin Panel'; ?> - Aarunya Admin</title>
    <link rel="stylesheet" href="../assets/css/premium-design-system.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Admin Dashboard Specific Styles */
        
        /* Stats Grid - 4 columns side by side */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 1400px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Glass Card */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            transition: all var(--transition-base);
        }
        
        .glass-card:hover {
            border-color: var(--primary-purple);
            box-shadow: var(--shadow-glow);
            transform: translateY(-4px);
        }
        
        /* Stat Card */
        .stat-card {
            cursor: pointer;
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .stat-content {
            flex: 1;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: var(--font-extrabold);
            color: var(--text-primary);
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: var(--font-sm);
            color: var(--text-secondary);
            font-weight: var(--font-medium);
            margin-bottom: 0.75rem;
        }
        
        .stat-trend {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: var(--font-xs);
            font-weight: var(--font-semibold);
        }
        
        .stat-trend.positive {
            color: var(--success);
        }
        
        .stat-trend.negative {
            color: var(--danger);
        }
        
        .stat-trend.neutral {
            color: var(--text-muted);
        }
        
        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: white;
        }
        
        .stat-icon.pink {
            background: var(--gradient-button);
        }
        
        .stat-icon.blue {
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
        }
        
        .stat-icon.purple {
            background: var(--gradient-button);
        }
        
        .stat-icon.green {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
        }
        
        .clickable-card {
            position: relative;
            overflow: hidden;
        }
        
        .card-hover-indicator {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--gradient-button);
            color: white;
            padding: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: var(--font-sm);
            font-weight: var(--font-semibold);
            transform: translateY(100%);
            transition: transform var(--transition-base);
        }
        
        .clickable-card:hover .card-hover-indicator {
            transform: translateY(0);
        }
        
        /* Grid Layouts */
        .grid {
            display: grid;
            gap: 1.5rem;
        }
        
        .grid-cols-2 {
            grid-template-columns: repeat(2, 1fr);
        }
        
        @media (max-width: 768px) {
            .grid-cols-2 {
                grid-template-columns: 1fr;
            }
        }
        
        /* Card Header */
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--divider);
        }
        
        .card-title {
            font-size: var(--font-xl);
            font-weight: var(--font-bold);
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0;
        }
        
        .card-title i {
            color: var(--primary-purple);
        }
        
        .view-all-link {
            color: var(--primary-purple);
            font-size: var(--font-sm);
            font-weight: var(--font-semibold);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: color var(--transition-fast);
        }
        
        .view-all-link:hover {
            color: var(--accent-cyan);
        }
        
        /* Table Styles */
        .table-container {
            overflow-x: auto;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table thead {
            background: rgba(196, 167, 255, 0.1);
        }
        
        .data-table th {
            padding: 1rem;
            text-align: left;
            font-size: var(--font-sm);
            font-weight: var(--font-semibold);
            color: var(--text-secondary);
            border-bottom: 1px solid var(--divider);
        }
        
        .data-table td {
            padding: 1rem;
            font-size: var(--font-sm);
            color: var(--text-primary);
            border-bottom: 1px solid var(--divider);
        }
        
        .data-table tbody tr {
            transition: background var(--transition-fast);
        }
        
        .data-table tbody tr:hover {
            background: rgba(196, 167, 255, 0.05);
        }
        
        /* User Info */
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--gradient-button);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: var(--font-bold);
            color: white;
            font-size: var(--font-sm);
        }
        
        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.375rem 0.875rem;
            font-size: var(--font-xs);
            font-weight: var(--font-semibold);
            border-radius: var(--radius-full);
        }
        
        .status-badge.confirmed {
            background: rgba(34, 197, 94, 0.15);
            color: var(--success);
            border: 1px solid rgba(34, 197, 94, 0.3);
        }
        
        .status-badge.pending {
            background: rgba(250, 204, 21, 0.15);
            color: var(--warning);
            border: 1px solid rgba(250, 204, 21, 0.3);
        }
        
        .status-badge.completed {
            background: rgba(59, 130, 246, 0.15);
            color: var(--info);
            border: 1px solid rgba(59, 130, 246, 0.3);
        }
        
        .status-badge.cancelled {
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        
        .status-badge.active {
            background: rgba(34, 197, 94, 0.15);
            color: var(--success);
            border: 1px solid rgba(34, 197, 94, 0.3);
        }
        
        /* Table Actions */
        .table-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: var(--radius-md);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all var(--transition-fast);
            font-size: var(--font-sm);
        }
        
        .action-btn.view {
            background: rgba(59, 130, 246, 0.15);
            color: #3B82F6;
        }
        
        .action-btn.view:hover {
            background: rgba(59, 130, 246, 0.25);
        }
        
        .action-btn.edit {
            background: rgba(250, 204, 21, 0.15);
            color: #FACC15;
        }
        
        .action-btn.edit:hover {
            background: rgba(250, 204, 21, 0.25);
        }
        
        .action-btn.approve {
            background: rgba(34, 197, 94, 0.15);
            color: var(--success);
        }
        
        .action-btn.approve:hover {
            background: rgba(34, 197, 94, 0.25);
        }
        
        .action-btn.delete {
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger);
        }
        
        .action-btn.delete:hover {
            background: rgba(239, 68, 68, 0.25);
        }
        
        /* Buttons */
        .btn-danger {
            background: linear-gradient(135deg, #DC2626 0%, #EF4444 100%);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            font-size: var(--font-base);
            font-weight: var(--font-semibold);
            border-radius: var(--radius-full);
            cursor: pointer;
            transition: all var(--transition-base);
            box-shadow: 0 4px 16px rgba(239, 68, 68, 0.4);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(239, 68, 68, 0.6);
        }
        
        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 56px;
            height: 56px;
            background: var(--gradient-button);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 1.25rem;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(127, 90, 240, 0.4);
            z-index: 999;
        }
        
        @media (max-width: 1024px) {
            .mobile-menu-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(8px);
        }
        
        .modal-content {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            margin: 5% auto;
            padding: 2rem;
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            width: 90%;
            max-width: 800px;
            box-shadow: var(--shadow-xl);
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--divider);
        }
        
        .modal-header h2 {
            font-size: var(--font-2xl);
            font-weight: var(--font-bold);
            color: var(--text-primary);
            margin: 0;
        }
        
        .close {
            color: var(--text-secondary);
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            transition: color var(--transition-fast);
            line-height: 1;
        }
        
        .close:hover {
            color: var(--primary-purple);
        }
        
        .modal-body {
            color: var(--text-primary);
        }
    </style>
</head>
<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
