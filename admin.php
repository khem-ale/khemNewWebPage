<?php
// Configuration
$uploads_dir = 'uploads';
if (!is_dir($uploads_dir)) {
    mkdir($uploads_dir, 0777, true);
}

// Initialize variables
$errors = [];
$portfolioItemName = '';
$portfolioItemDescription = '';
$portfolioItemPhoto = null;

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate Portfolio Item Name
    if (isset($_POST['portfolio-item-name'])) {
        $portfolioItemName = trim($_POST['portfolio-item-name']);
        if (empty($portfolioItemName)) {
            $errors[] = 'Portfolio item name is required.';
        }
    } else {
        $errors[] = 'Portfolio item name is missing.';
    }

    // Validate Portfolio Item Description
    if (isset($_POST['portfolio-item-description'])) {
        $portfolioItemDescription = trim($_POST['portfolio-item-description']);
        if (empty($portfolioItemDescription)) {
            $errors[] = 'Description is required.';
        }
    } else {
        $errors[] = 'Description is missing.';
    }

    // Validate Portfolio Item Photo
    if (isset($_FILES['portfolio-item-photo'])) {
        $portfolioItemPhoto = $_FILES['portfolio-item-photo'];
        if ($portfolioItemPhoto['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Error uploading photo.';
        } else {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($portfolioItemPhoto['type'], $allowedTypes)) {
                $errors[] = 'Only JPG, PNG, and GIF files are allowed.';
            }
        }
    } else {
        $errors[] = 'Photo is missing.';
    }

    // If no errors, process the form
    if (empty($errors)) {
        // Move uploaded file to the uploads directory
        $tmp_name = $portfolioItemPhoto['tmp_name'];
        $name = basename($portfolioItemPhoto['name']);
        $path = "$uploads_dir/$name";
        move_uploaded_file($tmp_name, $path);

        // Store portfolio item information (you could use a database here)
        $portfolioItems = [];
        if (file_exists('portfolio.json')) {
            $portfolioItems = json_decode(file_get_contents('portfolio.json'), true);
        }
        $portfolioItems[] = [
            'name' => $portfolioItemName,
            'description' => $portfolioItemDescription,
            'photo' => $path
        ];
        file_put_contents('portfolio.json', json_encode($portfolioItems));

        echo "Portfolio item added successfully!";
    } else {
        // Display errors
        foreach ($errors as $error) {
            echo "<p>$error</p>";
        }
    }
}

// Handle GET request to display portfolio items
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (file_exists('portfolio.json')) {
        $portfolioItems = json_decode(file_get_contents('portfolio.json'), true);
        foreach ($portfolioItems as $item) {
            echo "<tr>
                <td>{$item['name']}</td>
                <td>{$item['description']}</td>
                <td><img src='{$item['photo']}' alt='{$item['name']}' width='100'></td>
                <td><button>Delete</button></td>
            </tr>";
        }
    }
}
?>
