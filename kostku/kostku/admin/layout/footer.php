        </div>
    </div>

   
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
     const currentPath = window.location.pathname;
    const sidebarLinks = document.querySelectorAll("#sidebar ul li a");
            
     sidebarLinks.forEach(link => {
         const href = link.getAttribute("href");
            if (href && currentPath.includes(href)) {
             sidebarLinks.forEach(l => {
                 if (l.parentElement) {
                  l.parentElement.classList.remove("active");
               }
                 });
             if (link.parentElement) {
                 link.parentElement.classList.add("active");
                    }
                }
            });
        });
    </script>
</body>
</html>
