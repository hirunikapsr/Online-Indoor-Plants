/* ==============================================
   Main JavaScript - Online Indoor Plants
   File Path: js/main.js (or js/script.js)
   ============================================== */

// LocalStorage get Cart Data 
let cart = JSON.parse(localStorage.getItem("indoorPlantsCart")) || [];

// Get HTML Elements 
const cartCount = document.getElementById("cartCount");
const cartTotal = document.getElementById("cartTotal");
const searchInput = document.getElementById("searchInput");
const searchBtn = document.getElementById("searchBtn");
const resultText = document.getElementById("resultText");
const toast = document.getElementById("toast");


function money(value) {
    return "Rs. " + Number(value).toLocaleString("en-LK", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Cart UI and LocalStorage update 
function updateCartUI() {
    const count = cart.reduce((sum, item) => sum + item.qty, 0);
    const total = cart.reduce((sum, item) => sum + item.price * item.qty, 0);

    if (cartCount) cartCount.textContent = count;
    if (cartTotal) cartTotal.textContent = money(total);

    localStorage.setItem("indoorPlantsCart", JSON.stringify(cart));
}

// Show Toast Messages 
function showToast(message) {
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add("show");

    setTimeout(() => toast.classList.remove("show"), 1800);
}

// Add to Cart Buttons for Event Listeners
document.querySelectorAll(".add-cart, .add-to-cart-btn").forEach(button => {
    button.addEventListener("click", () => {
        const card = button.closest(".product-card");
        
        // Data Attributes or Content get Data
        const name = button.dataset.name || (card ? card.querySelector("h3").textContent : "Plant");
        const priceText = button.dataset.price || (card ? card.querySelector(".price").textContent.replace(/[^0-9.]/g, '') : "0");
        const price = Number(priceText);

        const existing = cart.find(item => item.name === name);

        if (existing) {
            existing.qty += 1;
        } else {
            cart.push({ name, price, qty: 1 });
        }

        updateCartUI();
        showToast(`${name} added to cart`);
    });
});

// Wishlist Buttons Toggle 
document.querySelectorAll(".wishlist, .wishlist-btn").forEach(button => {
    button.addEventListener("click", () => {
        button.classList.toggle("active");
        const icon = button.querySelector("i");
        if (icon) {
            if (button.classList.contains("active")) {
                icon.className = "fa-solid fa-heart";
                icon.style.color = "#e53935";
            } else {
                icon.className = "fa-regular fa-heart";
                icon.style.color = "";
            }
        }
    });
});

// Search and  Filter Functionality
function filterProducts(query = "") {
    const value = query.trim().toLowerCase();
    const cards = [...document.querySelectorAll(".product-card")];
    let visible = 0;

    cards.forEach(card => {
        const name = (card.dataset.name || card.querySelector("h3")?.textContent || "").toLowerCase();
        const category = (card.dataset.category || "").toLowerCase();

        const match = !value || name.includes(value) || category.includes(value);

        card.style.display = match ? "" : "none";
        if (match) visible++;
    });

    if (resultText) {
        resultText.textContent = value
            ? `${visible} plant${visible === 1 ? "" : "s"} found`
            : "Featured indoor plants";
    }

    const productsSection = document.getElementById("products") || document.querySelector(".popular-plants");
    if (productsSection && value) {
        productsSection.scrollIntoView({
            behavior: "smooth",
            block: "start"
        });
    }
}

// Search Button Listeners
if (searchBtn && searchInput) {
    searchBtn.addEventListener("click", () => filterProducts(searchInput.value));
}

if (searchInput) {
    searchInput.addEventListener("keydown", event => {
        if (event.key === "Enter") filterProducts(searchInput.value);
    });
}

// Category Cards Filter Listener
document.querySelectorAll(".category-card, .type-card").forEach(button => {
    button.addEventListener("click", () => {
        const categoryName = button.dataset.category || button.querySelector("h3")?.textContent || "";
        if (searchInput) searchInput.value = categoryName;
        filterProducts(categoryName);
    });
});

// View All Button
const viewAllBtn = document.getElementById("viewAllBtn");
if (viewAllBtn) {
    viewAllBtn.addEventListener("click", () => {
        if (searchInput) searchInput.value = "";

        document.querySelectorAll(".product-card").forEach(card => {
            card.style.display = "";
        });

        if (resultText) resultText.textContent = "Featured indoor plants";
        showToast("Showing all featured plants");
    });
}

// Cart Icon Navigation
const cartBtn = document.getElementById("cartBtn") || document.querySelector(".cart-icon");
if (cartBtn) {
    cartBtn.addEventListener("click", (e) => {
        // Cart Navigation 
        if (window.location.pathname.includes("pages")) {
            window.location.href = "cart.php";
        } else {
            window.location.href = "pages/cart.php";
        }
    });
}

// Login Icon Navigation
const loginBtn = document.getElementById("loginBtn");
if (loginBtn) {
    loginBtn.addEventListener("click", () => {
        if (window.location.pathname.includes("pages")) {
            window.location.href = "login.php";
        } else {
            window.location.href = "pages/login.php";
        }
    });
}

// Initial UI Update Call
updateCartUI();