            </div> 
        </div>
    </div> 

    <footer class="bg-white border-top py-4 mt-auto">
        <div class="container text-center">
            <p class="small text-muted mb-0">&copy; 2026 Kost & Kontrakan Pintar. Tugas Akhir Sistem Informasi Manajemen.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const currentPath = window.location.pathname;
                  const menuLinks = document.querySelectorAll(".list-group-item, .nav-link");
            
            menuLinks.forEach(link => {
                const href = link.getAttribute("href");
                if (href && currentPath.includes(href)) {
                    if (link.classList.contains("list-group-item")) {
                        link.classList.add("active");
                    } else if (link.classList.contains("nav-link")) {
                        link.classList.add("active");
                    }
                }
            });
        });
    </script>
</body>
</html>
