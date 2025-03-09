<?php include 'includes/header.php'; ?>

<div class="hero-section position-relative">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
    <div class="carousel-item active">
    <img src="https://images.pexels.com/photos/1290141/pexels-photo-1290141.jpeg" class="d-block w-100" alt="Artist Painting on Canvas" style="height: 500px; object-fit: cover;">

    </div>
    <div class="carousel-item">
    <img src="https://images.unsplash.com/photo-1506748686214-e9df14d4d9d0" class="d-block w-100" alt="Abstract Painting" style="height: 500px; object-fit: cover;">

    </div>
    <div class="carousel-item">
        <img src="https://images.unsplash.com/photo-1520607162513-77705c0f0d4a" class="d-block w-100" alt="Art Gallery Exhibition" style="height: 500px; object-fit: cover;">
    </div>
</div>

        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
    <div class="position-absolute top-50 start-50 translate-middle text-center text-white">
        <h1 class="display-4 fw-bold animate__animated animate__fadeInDown">Art Delivered</h1>
        <p class="lead animate__animated animate__fadeInUp">Order your favorite art from the best artists</p>
        <a href="menu.php" class="btn btn-danger btn-lg animate__animated animate__fadeInUp">Order Now</a>
    </div>
</div>

<div class="container my-5">
    <h2 class="text-center mb-4">Popular Categories</h2>
    <div class="row">
        <?php
        $query = "SELECT * FROM categories LIMIT 6";
        $result = mysqli_query($conn, $query);
        while ($category = mysqli_fetch_assoc($result)) {
        ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <img src="<?php echo $category['image'] ? $category['image'] : 'https://via.placeholder.com/300x200'; ?>" 
                     class="card-img-top" alt="<?php echo $category['name']; ?>" style="height: 200px; object-fit: cover;">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $category['name']; ?></h5>
                    <p class="card-text"><?php echo $category['description']; ?></p>
                    <a href="menu.php?category=<?php echo $category['id']; ?>" class="btn btn-outline-danger">View Items</a>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<div class="bg-light py-5">
    <div class="container">
        <h2 class="text-center mb-4">Why Choose Us?</h2>
        <div class="row">
            <div class="col-md-4 text-center mb-4">
                <div class="feature-card p-4 rounded shadow-sm">
                    <div class="icon-wrapper mb-3">
                        <i class="fas fa-truck fa-3x" style="color: var(--primary-color);"></i>
                    </div>
                    <h4>Fast Delivery</h4>
                    <p class="text-muted">Art will be delivered within 24 hours</p>
                </div>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="feature-card p-4 rounded shadow-sm">
                    <div class="icon-wrapper mb-3">
                        <i class="fas fa-paint-brush fa-3x" style="color: var(--primary-color);"></i>
                    </div>
                    <h4>Quality Art</h4>
                    <p class="text-muted">We partner with the best artists in town</p>
                </div>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="feature-card p-4 rounded shadow-sm">
                    <div class="icon-wrapper mb-3">
                        <i class="fas fa-headset fa-3x" style="color: var(--primary-color);"></i>
                    </div>
                    <h4>24/7 Support</h4>
                    <p class="text-muted">Our customer support team is always here to help</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hero-section {
    position: relative;
    height: 500px;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1;
}

.hero-section .position-absolute {
    z-index: 2;
}

.card {
    transition: transform 0.3s;
}

.card:hover {
    transform: translateY(-5px);
}

.feature-card {
    background-color: var(--card-bg);
    transition: all 0.3s ease;
    height: 100%;
    border: 1px solid var(--border-color);
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

.icon-wrapper {
    height: 80px;
    width: 80px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--bg-color);
    border-radius: 50%;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.feature-card:hover .icon-wrapper {
    transform: scale(1.1);
    box-shadow: 0 8px 20px rgba(142, 68, 173, 0.3);
}

.fas {
    transition: transform 0.3s;
}

.fas:hover {
    transform: scale(1.1);
}
</style>

<?php include 'includes/footer.php'; ?>
