<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ads Management</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<h1>Ads Management</h1>
<select id="sortOptions">
    <option value="price:asc">Price (Low to High)</option>
    <option value="price:desc">Price (High to Low)</option>
    <option value="created_at:asc">Date (Oldest First)</option>
    <option value="created_at:desc">Date (Newest First)</option>
</select>
<button id="applySort">Sort</button>

<table id="adsTable">
    <thead>
    <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Description</th>
        <th>Price</th>
        <th>Photos</th>
    </tr>
    </thead>
    <tbody>
    <!-- Data rows will be dynamically added here -->
    </tbody>
</table>
<h2>Ad Details</h2>
<div id="adDetails"></div>
<h2>Create New Ad</h2>
<form id="createAdForm">
    <input type="text" id="title" placeholder="Title" required>
    <textarea id="description" placeholder="Description" required></textarea>
    <input type="text" id="photos" placeholder="Photos (comma separated)" required>
    <input type="number" id="price" placeholder="Price" required>
    <button type="submit">Create</button>
</form>

<script src="assets/scripts.js"></script>
</body>
</html>