/* ==========================================================================
   ONLINE INDOOR PLANTS - MASTER JAVASCRIPT
   Interactivity, Live AJAX Search, Pot Variation Switcher
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {
  const baseUrl = window.SITE_BASE_URL || './';

  // 1. Mobile Navigation Toggle
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const navLinks = document.getElementById('navLinks');
  
  if (mobileMenuBtn && navLinks) {
    mobileMenuBtn.addEventListener('click', function () {
      navLinks.classList.toggle('active');
      const isExpanded = navLinks.classList.contains('active');
      mobileMenuBtn.innerHTML = isExpanded ? '✕' : '☰';
    });
  }

  // 2. Live AJAX Search Suggestions
  const searchInput = document.getElementById('headerSearchInput');
  const searchSuggestions = document.getElementById('searchSuggestions');
  let debounceTimer = null;

  if (searchInput && searchSuggestions) {
    searchInput.addEventListener('input', function () {
      const query = this.value.trim();
      clearTimeout(debounceTimer);

      if (query.length < 1) {
        searchSuggestions.style.display = 'none';
        searchSuggestions.innerHTML = '';
        return;
      }

      debounceTimer = setTimeout(() => {
        fetch(`${baseUrl}api/search.php?q=${encodeURIComponent(query)}`)
          .then(response => response.json())
          .then(data => {
            searchSuggestions.innerHTML = '';

            if (data && data.length > 0) {
              data.forEach(item => {
                const itemEl = document.createElement('div');
                itemEl.className = 'suggestion-item';
                itemEl.innerHTML = `
                  <img src="${baseUrl}${item.image}" alt="${item.name}" class="suggestion-img">
                  <div>
                    <div class="suggestion-name">${item.name}</div>
                    <div class="suggestion-cat">🌿 ${item.category}</div>
                  </div>
                  <div class="suggestion-price">${item.price}</div>
                `;

                // Clicking suggestion opens exact product detail page
                itemEl.addEventListener('click', function () {
                  window.location.href = `${baseUrl}pages/product-details.php?id=${item.id}`;
                });

                searchSuggestions.appendChild(itemEl);
              });
              searchSuggestions.style.display = 'block';
            } else {
              searchSuggestions.innerHTML = `
                <div style="padding:15px; text-align:center; color:var(--text-muted); font-size:0.9rem;">
                  No indoor plants found for "${query}"
                </div>
              `;
              searchSuggestions.style.display = 'block';
            }
          })
          .catch(err => {
            console.error('Search suggestion error:', err);
            searchSuggestions.style.display = 'none';
          });
      }, 200);
    });

    // Close suggestions dropdown when user clicks outside
    document.addEventListener('click', function (e) {
      if (!searchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
        searchSuggestions.style.display = 'none';
      }
    });
  }

  // 3. Quantity Selector Controls (Product Detail & Cart)
  const qtyBtns = document.querySelectorAll('.qty-btn');
  qtyBtns.forEach(btn => {
    btn.addEventListener('click', function () {
      const action = this.dataset.action;
      const input = this.parentElement.querySelector('input[type="number"]');
      if (input) {
        let val = parseInt(input.value) || 1;
        if (action === 'plus') {
          val++;
        } else if (action === 'minus' && val > 1) {
          val--;
        }
        input.value = val;
      }
    });
  });

  // 4. Price Filter Slider Display (Shop Page)
  const priceRange = document.getElementById('priceRange');
  const priceVal = document.getElementById('priceVal');
  if (priceRange && priceVal) {
    priceRange.addEventListener('input', function () {
      priceVal.textContent = 'Rs. ' + Math.round(parseFloat(this.value)).toLocaleString();
    });
  }

  

    // Initial check
    updateVariation();
  ;
});
