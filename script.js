let cart = JSON.parse(localStorage.getItem("indoorPlantsCart")) || [];

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

function updateCartUI() {
  const count = cart.reduce((sum, item) => sum + item.qty, 0);
  const total = cart.reduce((sum, item) => sum + item.price * item.qty, 0);

  cartCount.textContent = count;
  cartTotal.textContent = money(total);

  localStorage.setItem("indoorPlantsCart", JSON.stringify(cart));
}

function showToast(message) {
  toast.textContent = message;
  toast.classList.add("show");

  setTimeout(() => toast.classList.remove("show"), 1800);
}

document.querySelectorAll(".add-cart").forEach(button => {
  button.addEventListener("click", () => {
    const name = button.dataset.name;
    const price = Number(button.dataset.price);

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

document.querySelectorAll(".wishlist").forEach(button => {
  button.addEventListener("click", () => {
    button.classList.toggle("active");
    button.textContent = button.classList.contains("active") ? "♥" : "♡";
  });
});

function filterProducts(query = "") {
  const value = query.trim().toLowerCase();
  const cards = [...document.querySelectorAll(".product-card")];
  let visible = 0;

  cards.forEach(card => {
    const name = card.dataset.name.toLowerCase();
    const category = card.dataset.category.toLowerCase();

    const match = !value || name.includes(value) || category.includes(value);

    card.style.display = match ? "" : "none";

    if (match) visible++;
  });

  resultText.textContent = value
    ? `${visible} plant${visible === 1 ? "" : "s"} found`
    : "Featured indoor plants";

  document.getElementById("products").scrollIntoView({
    behavior: "smooth",
    block: "start"
  });
}

searchBtn.addEventListener("click", () => filterProducts(searchInput.value));

searchInput.addEventListener("keydown", event => {
  if (event.key === "Enter") filterProducts(searchInput.value);
});

document.querySelectorAll(".category-card").forEach(button => {
  button.addEventListener("click", () => {
    searchInput.value = button.dataset.category;
    filterProducts(button.dataset.category);
  });
});

document.getElementById("viewAllBtn").addEventListener("click", () => {
  searchInput.value = "";

  document.querySelectorAll(".product-card").forEach(card => {
    card.style.display = "";
  });

  resultText.textContent = "Featured indoor plants";
  showToast("Showing all featured plants");
});

document.getElementById("cartBtn").addEventListener("click", () => {
  if (cart.length === 0) {
    showToast("Your cart is empty");
    return;
  }

  const items = cart.map(item => `${item.name} x${item.qty}`).join(", ");
  showToast(items);
});

document.getElementById("loginBtn").addEventListener("click", () => {
  showToast("Login page can be connected here");
});

updateCartUI();
