    <footer class="py-5 mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="footer-section">
                        <h5 class="mb-3 footer-heading">About Us</h5>
                        <p class="mb-3">We deliver art right to your doorstep. Enjoy the best art from top artists in your area.</p>
                        <div class="social-icons">
                            <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-pinterest"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="footer-section">
                        <h5 class="mb-3 footer-heading">Quick Links</h5>
                        <ul class="list-unstyled footer-links">
                            <li><a href="index.php"><i class="fas fa-angle-right me-2"></i>Home</a></li>
                            <li><a href="menu.php"><i class="fas fa-angle-right me-2"></i>Menu</a></li>
                            <li><a href="#"><i class="fas fa-angle-right me-2"></i>Contact Us</a></li>
                            <li><a href="#"><i class="fas fa-angle-right me-2"></i>Terms & Conditions</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="footer-section">
                        <h5 class="mb-3 footer-heading">Contact Info</h5>
                        <ul class="list-unstyled footer-contact">
                            <li><i class="fas fa-phone me-2"></i> +1 234 567 890</li>
                            <li><i class="fas fa-envelope me-2"></i> info@artdelivery.com</li>
                            <li><i class="fas fa-map-marker-alt me-2"></i> 123 Art Street, City</li>
                        </ul>
                        <div class="newsletter mt-3">
                            <h6 class="mb-2">Subscribe to our newsletter</h6>
                            <div class="input-group">
                                <input type="email" class="form-control" placeholder="Your email">
                                <button class="btn btn-primary" type="button">Subscribe</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="my-4" style="border-color: var(--border-color); opacity: 0.1;">
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Art Delivery. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Check for saved theme preference or use default dark theme
        const currentTheme = localStorage.getItem('theme') ? localStorage.getItem('theme') : 'dark';
        
        // Apply the saved theme on page load
        document.documentElement.setAttribute('data-theme', currentTheme);
        
        // Update checkbox state based on current theme
        if (currentTheme === 'dark') {
            document.getElementById('checkbox').checked = true;
        }
        
        // Theme toggle functionality
        document.getElementById('checkbox').addEventListener('change', function() {
            if (this.checked) {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                animateThemeChange('dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
                animateThemeChange('light');
            }
        });
        
        // Add subtle animation when theme changes
        function animateThemeChange(theme) {
            const body = document.body;
            body.style.transition = 'background-color 0.5s ease';
            
            // Add a subtle animation to cards and other elements
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                card.classList.add('animate__animated', 'animate__fadeIn');
                setTimeout(() => {
                    card.classList.remove('animate__animated', 'animate__fadeIn');
                }, 500);
            });
        }
        
        // Add active class to current page in navigation
        document.addEventListener('DOMContentLoaded', function() {
            const currentLocation = window.location.pathname;
            const navLinks = document.querySelectorAll('.nav-link');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentLocation.substring(currentLocation.lastIndexOf('/') + 1)) {
                    link.classList.add('active');
                    link.style.color = 'var(--primary-color) !important';
                }
            });
        });
    </script>
    <style>
        footer {
            background-color: var(--footer-bg);
            color: var(--footer-text);
        }
        
        .footer-heading {
            position: relative;
            padding-bottom: 10px;
            font-weight: 600;
        }
        
        .footer-heading::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 2px;
            background-color: var(--primary-color);
        }
        
        .footer-links li {
            margin-bottom: 10px;
        }
        
        .footer-links a, .footer-contact li {
            color: var(--footer-text);
            opacity: 0.8;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .footer-links a:hover {
            opacity: 1;
            color: var(--primary-color);
            padding-left: 5px;
        }
        
        .footer-contact li {
            margin-bottom: 10px;
        }
        
        .social-icons {
            display: flex;
            gap: 10px;
        }
        
        .social-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--footer-text);
            transition: all 0.3s ease;
        }
        
        .social-icon:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-3px);
        }
        
        .newsletter .form-control {
            background-color: rgba(255, 255, 255, 0.1);
            border: none;
            color: var(--footer-text);
        }
        
        .newsletter .form-control::placeholder {
            color: var(--footer-text);
            opacity: 0.7;
        }
        
        .newsletter .form-control:focus {
            box-shadow: none;
            background-color: rgba(255, 255, 255, 0.15);
        }
        
        .footer-section {
            height: 100%;
        }
        
        @media (max-width: 768px) {
            .footer-section {
                text-align: center;
            }
            
            .footer-heading::after {
                left: 50%;
                transform: translateX(-50%);
            }
            
            .social-icons {
                justify-content: center;
            }
            
            .footer-links a:hover {
                padding-left: 0;
            }
        }
    </style>
</body>
</html>
