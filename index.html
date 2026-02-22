<?php
session_start();

// จัดการการล้างข้อมูล
if (isset($_POST['reset'])) {
    $_SESSION['tree_data'] = [];
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// จัดการการเพิ่มข้อมูล
if (isset($_POST['node_value']) && $_POST['node_value'] !== "") {
    $val = intval($_POST['node_value']);
    if (!isset($_SESSION['tree_data'])) {
        $_SESSION['tree_data'] = [];
    }
    $_SESSION['tree_data'][] = $val;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// เตรียมข้อมูลส่งให้ JavaScript
$tree_nodes = isset($_SESSION['tree_data']) ? json_encode($_SESSION['tree_data']) : "[]";
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Binary Search Tree Visualizer</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #a855f7;
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --glass-bg: rgba(255, 255, 255, 0.95);
            --node-color: #ffffff;
            --line-color: #cbd5e1;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background: var(--bg-gradient);
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            color: #1e293b;
        }

        .container {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 30px;
            width: 95%;
            max-width: 1000px;
            text-align: center;
        }

        h1 { color: #4338ca; margin-bottom: 25px; font-weight: 500; }

        .controls {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        input {
            padding: 12px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            outline: none;
            width: 120px;
            font-size: 16px;
        }

        button {
            padding: 12px 25px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
            font-size: 16px;
        }

        .btn-insert { background: var(--primary-color); color: white; }
        .btn-reset { background: #ef4444; color: white; }
        button:hover { transform: translateY(-2px); opacity: 0.9; }

        #tree-container {
            width: 100%;
            height: 400px;
            border: 1px dashed #cbd5e1;
            border-radius: 15px;
            margin-bottom: 30px;
            overflow: auto;
            background: #f8fafc;
            position: relative;
        }

        .traversal-results {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 15px;
            text-align: left;
        }

        .traversal-card {
            background: white;
            padding: 15px;
            border-radius: 12px;
            border-left: 5px solid var(--secondary-color);
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .traversal-card h3 { margin: 0 0 5px 0; font-size: 14px; color: #64748b; text-transform: uppercase; }
        .traversal-card p { margin: 0; font-weight: 500; color: #1e293b; word-break: break-all; }

        circle { fill: white; stroke: var(--primary-color); stroke-width: 3px; }
        text { font-family: 'Kanit', sans-serif; font-size: 14px; font-weight: 500; text-anchor: middle; dominant-baseline: middle; }
        line { stroke: var(--line-color); stroke-width: 2px; }
    </style>
</head>
<body>

<div class="container">
    <h1>Binary Search Tree (PHP Version)</h1>
    
    <form method="POST" class="controls">
        <input type="number" name="node_value" placeholder="ระบุตัวเลข" required autofocus>
        <button type="submit" class="btn-insert">เพิ่มข้อมูล</button>
        <button type="submit" name="reset" class="btn-reset">ล้างข้อมูล</button>
    </form>

    <div id="tree-container">
        <svg id="tree-svg" width="1000" height="400"></svg>
    </div>

    <div class="traversal-results">
        <div class="traversal-card">
            <h3>Preorder</h3>
            <p id="preorder">-</p>
        </div>
        <div class="traversal-card">
            <h3>Inorder</h3>
            <p id="inorder">-</p>
        </div>
        <div class="traversal-card">
            <h3>Postorder</h3>
            <p id="postorder">-</p>
        </div>
    </div>
</div>

<script>
    // รับข้อมูลจาก PHP
    const initialData = <?php echo $tree_nodes; ?>;

    class Node {
        constructor(value) {
            this.value = value;
            this.left = null;
            this.right = null;
        }
    }

    let
