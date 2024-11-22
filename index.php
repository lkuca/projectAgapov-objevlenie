<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ads Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        form {
            margin-bottom: 20px;
        }
        input, select, button {
            padding: 5px;
            margin-right: 5px;
        }
    </style>
</head>
<body>
<h1>Ads Management</h1>

<!-- Form for creating new ad -->
<h2>Create New Ad</h2>
<form id="createAdForm">
    <label for="title">Title:</label>
    <input type="text" id="title" name="title" required><br>

    <label for="description">Description:</label>
    <textarea id="description" name="description" required></textarea><br>

    <label for="photos">Photos (comma separated):</label>
    <input type="text" id="photos" name="photos" required><br>

    <label for="price">Price:</label>
    <input type="number" id="price" name="price" required><br>

    <button type="button" onclick="createAd()">Create Ad</button>
</form>

<!-- Form for fetching ads -->
<h2>Fetch Ads</h2>
<form id="fetchAdsForm">
    <label for="page">Page:</label>
    <input type="number" id="page" name="page" value="1" min="1">

    <label for="sort">Sort By:</label>
    <select id="sort" name="sort">
        <option value="created_at_desc">Newest</option>
        <option value="price_asc">Price (Low to High)</option>
        <option value="price_desc">Price (High to Low)</option>
    </select>

    <button type="button" onclick="fetchAds()">Fetch Ads</button>
</form>

<!-- Table for displaying ads -->
<h2>Ads List</h2>
<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Price</th>
        <th>Main Photo</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody id="adsTableBody">
    <!-- Ads will be dynamically added here -->
    </tbody>
</table>

<!-- Form for fetching one ad -->
<h2>Fetch Single Ad</h2>
<form id="fetchAdForm">
    <label for="adId">Ad ID:</label>
    <input type="number" id="adId" name="adId" required><br>

    <button type="button" onclick="fetchSingleAd()">Fetch Ad</button>
</form>

<div id="adDetails"></div>

<script>
    // Fetch all ads
    function fetchAds() {
        const page = document.getElementById('page').value;
        const sort = document.getElementById('sort').value;

        fetch(`/api/ads?page=${page}&sort=${sort}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                const adsTableBody = document.getElementById('adsTableBody');
                adsTableBody.innerHTML = ''; // Clear previous rows

                data.forEach(ad => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                            <td>${ad.id}</td>
                            <td>${ad.title}</td>
                            <td>${ad.price}</td>
                            <td><img src="${ad.main_photo}" alt="Main Photo" width="100"></td>
                            <td><button onclick="fetchSingleAd(${ad.id})">View</button></td>
                        `;
                    adsTableBody.appendChild(row);
                });
            })
            .catch(error => {
                alert(`Error: ${error.message}`);
            });
    }

    // Fetch single ad
    function fetchSingleAd(adId = null) {
        if (!adId) {
            adId = document.getElementById('adId').value;
        }

        fetch(`/api/ads/${adId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(ad => {
                const adDetails = document.getElementById('adDetails');
                adDetails.innerHTML = `
                    <h3>${ad.title}</h3>
                    <p><strong>Description:</strong> ${ad.description}</p>
                    <p><strong>Price:</strong> $${ad.price}</p>
                    <p><strong>Photos:</strong> ${ad.photos ? ad.photos.join(', ') : 'No photos available'}</p>
                `;
            })
            .catch(error => {
                alert(`Error: ${error.message}`);
            });
    }

    // Create new ad
    function createAd() {
        const title = document.getElementById('title').value;
        const description = document.getElementById('description').value;
        const photos = document.getElementById('photos').value.split(',');
        const price = document.getElementById('price').value;

        const newAd = {
            title: title,
            description: description,
            photos: photos,
            price: price
        };

        fetch('/api/ads', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(newAd)
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Ad created successfully!');
                    fetchAds(); // Refresh ads list
                } else {
                    alert('Error creating ad');
                }
            })
            .catch(error => {
                alert(`Error: ${error.message}`);
            });
    }
</script>
</body>
</html>