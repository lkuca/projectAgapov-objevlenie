document.addEventListener('DOMContentLoaded', () => {
    const adsTable = document.querySelector('#adsTable tbody');
    const createAdForm = document.querySelector('#createAdForm');

    // Fetch and display ads
    function viewAd(adId) {
        fetch(`/api/ads.php?id=${adId}`)
            .then(response => response.json())
            .then(ad => {
                const adDetails = document.getElementById('adDetails');
                // Очищаем блок перед добавлением новых данных
                adDetails.innerHTML = `
                <h3>${ad.title}</h3>
                <p><strong>Description:</strong> ${ad.description}</p>
                <p><strong>Price:</strong> $${ad.price}</p>
                <p><strong>Photos:</strong></p>
            `;

                // Для каждого фото создаем тег <img>
                ad.photos.forEach(photo => {
                    const img = document.createElement('img');
                    img.src = photo;  // Устанавливаем источник изображения
                    img.alt = 'Ad photo';  // Альтернативный текст
                    img.style.width = '200px';  // Устанавливаем ширину изображения
                    img.style.marginRight = '10px';  // Отступ между картинками
                    adDetails.appendChild(img);  // Добавляем картинку в блок
                });
            })
            .catch(error => {
                alert('Error fetching ad: ' + error.message);
            });
    }

    // Load and display ads
    function loadAds() {
        fetch('/api/ads.php')
            .then(res => res.json())
            .then(ads => {
                const adsTableBody = document.querySelector('#adsTable tbody');
                adsTableBody.innerHTML = '';

                ads.forEach(ad => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                    <td>${ad.id}</td>
                    <td>${ad.title}</td>
                    <td>${ad.description}</td>
                    <td>${ad.price}</td>
                    <td><img src="${ad.photos[0]}" alt="photo" width="100"></td>
                    <td><button class="viewAdBtn" data-id="${ad.id}">View</button></td>
                    `;
                    adsTableBody.appendChild(row);
                });

                const viewAdButtons = document.querySelectorAll('.viewAdBtn');
                viewAdButtons.forEach(button => {
                    button.addEventListener('click', function () {
                        const adId = button.getAttribute('data-id');
                        viewAd(adId);
                    });
                });
            })
            .catch(err => console.error(err));
    }

    // Handle sorting
    document.getElementById('applySort').addEventListener('click', () => {
        const sortOption = document.getElementById('sortOptions').value;
        const [sortBy, order] = sortOption.split(':');

        fetch(`/api/ads.php?sort_by=${sortBy}&order=${order}`)
            .then(response => response.json())
            .then(ads => {
                const adsTableBody = document.querySelector('#adsTable tbody');
                adsTableBody.innerHTML = ''; // Clear table

                ads.forEach(ad => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                    <td>${ad.id}</td>
                    <td>${ad.title}</td>
                    <td>${ad.description}</td>
                    <td>${ad.price}</td>
                    <td><img src="${ad.photos[0]}" alt="photo" width="100"></td>
                    <td><button class="viewAdBtn" data-id="${ad.id}">View</button></td>
                    `;
                    adsTableBody.appendChild(row);
                });

                const viewAdButtons = document.querySelectorAll('.viewAdBtn');
                viewAdButtons.forEach(button => {
                    button.addEventListener('click', function () {
                        const adId = button.getAttribute('data-id');
                        viewAd(adId);
                    });
                });
            })
            .catch(error => console.error('Error fetching ads:', error));
    });

    // Load ads on page load
    loadAds();

    // Handle ad creation
    createAdForm.addEventListener('submit', (e) => {
        e.preventDefault();

        const title = document.querySelector('#title').value.trim();
        const description = document.querySelector('#description').value.trim();
        const photos = document.querySelector('#photos').value.split(',').map(photo => photo.trim());
        const price = document.querySelector('#price').value.trim();

        if (title.length === 0 || title.length > 200) {
            alert('Title must be between 1 and 200 characters.');
            return;
        }

        if (description.length === 0 || description.length > 1000) {
            alert('Description must be between 1 and 1000 characters.');
            return;
        }

        if (photos.length === 0 || photos.length > 3) {
            alert('You must provide between 1 and 3 photo links.');
            return;
        }

        if (!photos.every(photo => photo.startsWith('http'))) {
            alert('Each photo link must be a valid URL starting with "http".');
            return;
        }

        if (isNaN(price) || price <= 0) {
            alert('Price must be a positive number.');
            return;
        }

        fetch('/api/ads.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({title, description, photos, price})
        })
            .then(res => res.json())
            .then(data => {
                alert('Ad created successfully!');
                createAdForm.reset();
                loadAds();
            })
            .catch(err => console.error(err));
    });
});

