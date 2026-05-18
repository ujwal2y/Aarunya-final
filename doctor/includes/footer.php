        </div><!-- End main-content -->
    </div><!-- End app-layout -->

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
        <i class="fas fa-bars"></i>
    </button>

    <script>
    function toggleMobileMenu() {
        document.querySelector('.sidebar').classList.toggle('mobile-open');
    }

    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.sidebar');
        const toggle = document.querySelector('.mobile-menu-toggle');
        
        if (window.innerWidth <= 1024 && 
            !sidebar.contains(event.target) && 
            !toggle.contains(event.target) &&
            sidebar.classList.contains('mobile-open')) {
            sidebar.classList.remove('mobile-open');
        }
    });
    </script>
</body>
</html>
