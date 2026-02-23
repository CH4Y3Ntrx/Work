<?php
/**
 * 1. ส่วนการเชื่อมต่อฐานข้อมูล (PHP)
 * ดึงค่าจาก Environment Variables ใน Vercel/Netlify
 */
$host = getenv('DB_HOST') ?: '202.29.70.18'; 
$port = getenv('DB_PORT') ?: '28211'; 
$user = getenv('DB_USER') ?: 'Index';
$pass = getenv('DB_PASSWORD') ?: 'lucky.0623044632oko'; 
$db   = getenv('DB_NAME') ?: 'Index';

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// จัดการการล้างข้อมูล
if (isset($_POST['reset'])) {
    $conn->query("DELETE FROM bst_nodes");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// จัดการการเพิ่มข้อมูล
if (isset($_POST['node_value']) && $_POST['node_value'] !== "") {
    $val = intval($_POST['node_value']);
    $stmt = $conn->prepare("INSERT INTO bst_nodes (node_value) VALUES (?)");
    $stmt->bind_param("i", $val);
    $stmt->execute();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// ดึงข้อมูลทั้งหมดมาเตรียมวาด Tree
$result = $conn->query("SELECT node_value FROM bst_nodes ORDER BY created_at ASC");
$nodes_array = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $nodes_array[] = intval($row['node_value']);
    }
}
$tree_nodes = json_encode($nodes_array);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern BST Visualizer (MariaDB)</title>
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
    <h1>Binary Search Tree (MariaDB)</h1>
    
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
    /**
     * 3. ส่วน JavaScript Logic
     */
    const initialData = <?php echo $tree_nodes; ?>;

    class Node {
        constructor(value) {
            this.value = value;
            this.left = null;
            this.right = null;
        }
    }

    let root = null;
    const svg = document.getElementById('tree-svg');

    function locallyInsertNode(val) {
        const newNode = new Node(val);
        if (!root) {
            root = newNode;
        } else {
            addNode(root, newNode);
        }
    }

    function addNode(node, newNode) {
        if (newNode.value < node.value) {
            if (!node.left) node.left = newNode;
            else addNode(node.left, newNode);
        } else {
            if (!node.right) node.right = newNode;
            else addNode(node.right, newNode);
        }
    }

    function updateVisualization() {
        svg.innerHTML = '';
        if (root) {
            drawTree(root, 500, 50, 200);
        }
        updateTraversals();
    }

    function drawTree(node, x, y, spacing) {
        if (node.left) {
            const lx = x - spacing;
            const ly = y + 70;
            drawLine(x, y, lx, ly);
            drawTree(node.left, lx, ly, spacing / 1.8);
        }
        if (node.right) {
            const rx = x + spacing;
            const ry = y + 70;
            drawLine(x, y, rx, ry);
            drawTree(node.right, rx, ry, spacing / 1.8);
        }
        drawNode(x, y, node.value);
    }

    function drawNode(x, y, val) {
        const g = document.createElementNS("http://www.w3.org/2000/svg", "g");
        const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
        circle.setAttribute("cx", x); circle.setAttribute("cy", y); circle.setAttribute("r", 20);
        const text = document.createElementNS("http://www.w3.org/2000/svg", "text");
        text.setAttribute("x", x); text.setAttribute("y", y);
        text.textContent = val;
        g.appendChild(circle); g.appendChild(text);
        svg.appendChild(g);
    }

    function drawLine(x1, y1, x2, y2) {
        const line = document.createElementNS("http://www.w3.org/2000/svg", "line");
        line.setAttribute("x1", x1); line.setAttribute("y1", y1);
        line.setAttribute("x2", x2); line.setAttribute("y2", y2);
        svg.appendChild(line);
    }

    function updateTraversals() {
        let pre = [], ino = [], post = [];
        function getPre(n) { if(!n) return; pre.push(n.value); getPre(n.left); getPre(n.right); }
        function getIn(n) { if(!n) return; getIn(n.left); ino.push(n.value); getIn(n.right); }
        function getPost(n) { if(!n) return; getPost(n.left); getPost(n.right); post.push(n.value); }
        getPre(root); getIn(root); getPost(root);
        document.getElementById('preorder').innerText = pre.join(' → ') || '-';
        document.getElementById('inorder').innerText = ino.join(' → ') || '-';
        document.getElementById('postorder').innerText = post.join(' → ') || '-';
    }

    // เมื่อหน้าเว็บโหลด ให้เอาข้อมูลจาก DB มาวาดทันที
    window.onload = () => {
        if (initialData && initialData.length > 0) {
            initialData.forEach(val => locallyInsertNode(val));
            updateVisualization();
        }
    };
</script>

</body>
</html>
