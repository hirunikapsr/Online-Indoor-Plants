/*
    include.js
    -------------------------------------------------------------
    Small helper used only by the NEW pages (shop-air-purifying.html,
    plant-details.html). It does two things:

    1. Loads header.html / footer.html into any page that has
       <div id="site-header"></div> and <div id="site-footer"></div>.

    2. Wires up the small interactive bits on the Plant Details page
       (quantity stepper, color swatch selection).

    This file is separate from script.js on purpose, so the existing
    file is never touched.

    NOTE: fetch() needs the page to be served over http/https. If you
    open the .html file directly from disk (file://), most browsers
    block the fetch and the header/footer area will stay empty. Use
    a local server (VS Code "Live Server", `python -m http.server`,
    etc.) or your GitHub Pages deployment to preview.
*/

async function includeHTML(selector, file) {
    const target = document.querySelector(selector);
    if (!target) return;

    try {
        const response = await fetch(file);
        if (!response.ok) throw new Error(file + " not found");
        target.innerHTML = await response.text();
    } catch (err) {
        console.error("Could not load " + file + ". Serve this site over http:// instead of opening the file directly.", err);
    }
}

document.addEventListener("DOMContentLoaded", () => {
    Promise.all([
        includeHTML("#site-header", "header.html"),
        includeHTML("#site-footer", "footer.html")
    ]).then(initPageScripts);
});

function initPageScripts() {
    // Quantity stepper (Plant Details page)
    document.querySelectorAll(".qty-stepper").forEach(stepper => {
        const minus = stepper.querySelector(".qty-minus");
        const plus = stepper.querySelector(".qty-plus");
        const input = stepper.querySelector(".qty-input");

        if (minus && input) {
            minus.addEventListener("click", () => {
                input.value = Math.max(1, Number(input.value) - 1);
            });
        }

        if (plus && input) {
            plus.addEventListener("click", () => {
                input.value = Number(input.value) + 1;
            });
        }
    });

    // Color swatch selection (Plant Details page)
    document.querySelectorAll(".color-swatch").forEach(swatch => {
        swatch.addEventListener("click", () => {
            swatch.parentElement
                .querySelectorAll(".color-swatch")
                .forEach(s => s.classList.remove("selected"));
            swatch.classList.add("selected");
        });
    });

    // Cancel button on Plant Details page
    document.querySelectorAll(".btn-cancel").forEach(btn => {
        btn.addEventListener("click", () => {
            window.history.back();
        });
    });
}
