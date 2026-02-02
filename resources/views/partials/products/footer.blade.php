  <script>
      // =========================================================
      // ✅ فلترة + بحث المنتجات
      // =========================================================
      const chips = document.getElementById("chips");
      const cards = [...document.querySelectorAll(".cardx")];
      const input = document.getElementById("searchInput");
      const searchBtn = document.getElementById("searchBtn");

      let currentFilter = "all";

      function applyFilter() {
          const q = (input.value || "").trim().toLowerCase();

          cards.forEach(card => {
              const cat = card.getAttribute("data-cat");
              const title = (card.getAttribute("data-title") || "").toLowerCase();
              const text = (card.innerText || "").toLowerCase();

              const matchCat = (currentFilter === "all") || (cat === currentFilter);
              const matchQ = !q || title.includes(q) || text.includes(q);

              card.style.display = (matchCat && matchQ) ? "" : "none";
          });
      }

      chips.addEventListener("click", (e) => {
          const btn = e.target.closest(".chip");
          if (!btn) return;

          document.querySelectorAll(".chip").forEach(b => b.classList.remove("active"));
          btn.classList.add("active");
          currentFilter = btn.dataset.filter;
          applyFilter();
      });

      input.addEventListener("input", applyFilter);
      searchBtn.addEventListener("click", applyFilter);

      window.addEventListener("load", () => {
          cards.forEach((c, i) => c.style.animationDelay = (0.05 + i * 0.05) + "s");
          applyFilter();
      });
  </script>
