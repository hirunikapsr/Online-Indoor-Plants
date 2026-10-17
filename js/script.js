/* ==========================================================================
   ONLINE INDOOR PLANTS - MASTER JAVASCRIPT
   Interactivity, Live AJAX Search, Pot Variation Switcher
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {
  const baseUrl = window.SITE_BASE_URL || './';

// Form Validation: Friendly Alerts, Green Checkmarks & Prevent Submit
  const forms = document.querySelectorAll('form');
  forms.forEach(form => {
    const inputs = form.querySelectorAll('input[required]');
    inputs.forEach(input => {
      // Create element for friendly alert
      const alertMsg = document.createElement('div');
      alertMsg.style.color = '#ef4444';
      alertMsg.style.fontSize = '0.85rem';
      alertMsg.style.marginTop = '4px';
      alertMsg.style.display = 'none';
      input.parentNode.insertBefore(alertMsg, input.nextSibling);

      input.addEventListener('input', function() {
        let isValid = this.checkValidity();
        let errorMessage = "";

        // Custom validation specifically for Phone Number
        if (this.name === 'phone') {
          const phoneVal = this.value.replace(/\s+/g, ''); 
          const isOnlyNumbers = /^\+?[0-9]+$/.test(phoneVal); 
          
          if (this.value === '') {
            isValid = false;
            errorMessage = "This field cannot be empty. Please fill it out.";
          } else if (!isOnlyNumbers || phoneVal.length < 10) {
            isValid = false;
            errorMessage = "Please enter a valid 10-digit phone number (e.g., 077 123 4567)";
          } else {
            isValid = true;
          }
        } 
        // Custom validation for Email
        else if (this.type === 'email') {
          if (this.validity.typeMismatch) {
            isValid = false;
            errorMessage = "Please enter a valid email address (e.g., hello@example.com)";
          } else if (this.validity.valueMissing) {
            errorMessage = "This field cannot be empty. Please fill it out.";
          }
        } 
        // Default validation for other text fields
        else {
          if (this.validity.valueMissing) {
            errorMessage = "This field cannot be empty. Please fill it out.";
          }
        }

        // Apply styles and block/allow form submission
        if (isValid) {
          this.style.border = '2px solid #10b981'; // Green validation check
          alertMsg.style.display = 'none';
          this.setCustomValidity(''); // Allows form to submit
        } else {
          this.style.border = '2px solid #ef4444'; // Red error alert
          alertMsg.style.display = 'block';
          alertMsg.textContent = errorMessage;
          this.setCustomValidity(errorMessage); // Blocks form submission
        }
      });
    });
  });

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

 // 3. Quantity Selector Controls & LocalStorage Cart Fix
  const qtyBtns = document.querySelectorAll('.qty-btn');
  qtyBtns.forEach(btn => {
    btn.addEventListener('click', function () {
      const action = this.dataset.action;
      const input = this.parentElement.querySelector('input[type="number"]');
      if (input) {
        // Fix: Use parseInt / parseFloat to prevent NaN errors
        let val = parseFloat(input.value);
        if (isNaN(val)) val = 1; 

        if (action === 'plus') {
          val++;
        } else if (action === 'minus' && val > 1) { // Disallow negative quantities
          val--;
        }
        input.value = val;

        // Fix: Save cart updates to localStorage
        localStorage.setItem('saved_cart_qty', val);
        
        // Auto submit form in cart page to update counter & subtotal immediately
        if(this.closest('form') && window.location.href.includes('cart.php')) {
            this.closest('form').submit();
        }
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

  // 5. Product Detail - Pot Type & Colour Variation Switcher
window.initProductVariationSwitcher = function (basePrice, variationsData) {
  const mainImg = document.getElementById('mainProductImg');
  const priceDisplay = document.getElementById('productPriceDisplay');
  const variationIdInput = document.getElementById('selectedVariationId');
  const stockDisplay = document.getElementById('variationStockDisplay');

  function updateVariation() {
    
    const activePotTypeBtn = document.querySelector('.pot-type-btn.active');
    const activePotColourBtn = document.querySelector('.pot-colour-btn.active');

    if (!activePotTypeBtn || !activePotColourBtn) return;

    const selectedPotType = Number(activePotTypeBtn.dataset.typeId);
    const selectedPotColour = Number(activePotColourBtn.dataset.colourId);

    
    let match = variationsData.find(v => 
      Number(v.pot_type_id) === selectedPotType && 
      Number(v.pot_colour_id) === selectedPotColour
    );

    
    if (!match) {
      match = variationsData.find(v => Number(v.pot_type_id) === selectedPotType);
    }

    if (match) {
      const variationImgPath = match.image || match.variation_image || match.img;
      // Update Main Image 
      if (mainImg && variationImgPath) {
        mainImg.style.opacity = '0.3';
        setTimeout(() => {
          let imagePath = variationImgPath;

          if (!imagePath.startsWith('../') && !imagePath.startsWith('http') && !imagePath.startsWith('/')) {
            imagePath = '../' + imagePath.replace(/^\/+/, '');
          }

          
          mainImg.src = encodeURI(imagePath);
          mainImg.style.opacity = '1';
        }, 100);
      }

      // Price Display Update
      if (priceDisplay) {
        const finalPrice = basePrice + parseFloat(match.additional_price || 0);
        priceDisplay.textContent = 'Rs. ' + Math.round(finalPrice).toLocaleString();
      }

      // Update Hidden variation_id input 
      if (variationIdInput) {
        variationIdInput.value = match.variation_id;
      }

      // Stock status update
      if (stockDisplay) {
        const stockQty = Number(match.stock_quantity || 0);
        stockDisplay.textContent = stockQty > 0 ? 
          `In Stock (${stockQty} available)` : 
          'Out of Stock';
        stockDisplay.style.color = stockQty > 0 ? '#10b981' : '#ef4444';
      }
    }
    
  };

  // Event Listener Attaching Function (Include Event Delegation support)
  function attachSelectionEvents(selector, activeClass) {
    document.querySelectorAll(selector).forEach(btn => {
      btn.onclick = function (e) {
        e.preventDefault(); // Form submission
        e.stopPropagation();

        document.querySelectorAll(selector).forEach(b => b.classList.remove(activeClass));
        this.classList.add(activeClass);
        updateVariation();
      };
    });
  }

  
  attachSelectionEvents('.pot-type-btn', 'active');
  attachSelectionEvents('.pot-colour-btn', 'active');

  
  updateVariation();
};
});