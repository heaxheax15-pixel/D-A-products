/* Cascading Wilaya/Commune selects for the checkout address (offline, no deps).
   The dataset (assets/data/algeria-cities.json) is a light array of
   {"w":"<wilaya ar>","wc":"<code>","c":"<commune ar>"}.

   Progressive enhancement:
   - If the dataset cannot be loaded, the manual "customer_address" textarea
     stays visible and required, so the order can always be completed.
   - Once loaded, the selects take over and the manual textarea is disabled. */
(function () {
    "use strict";

    function ready(fn) {
        if (document.readyState !== "loading") fn();
        else document.addEventListener("DOMContentLoaded", fn);
    }

    ready(function () {
        var wilayaSelect = document.getElementById("wilaya");
        var communeSelect = document.getElementById("commune");
        var communeRow = document.getElementById("communeRow");
        var detailsRow = document.getElementById("addressDetailsRow");
        var detailsInput = document.getElementById("address_details");
        var fallbackRow = document.getElementById("addressFallbackRow");
        var fallbackText = document.getElementById("customer_address_fallback");
        var hidden = document.getElementById("customer_address");

        if (!wilayaSelect || !communeSelect || !hidden) return;

        var cities = [];

        function enableLocationUI() {
            // Selects are now the source of truth; hide the manual textarea and
            // take its name away so it is not submitted alongside the hidden
            // field (both would otherwise post as customer_address).
            if (fallbackRow) fallbackRow.hidden = true;
            if (fallbackText) {
                fallbackText.required = false;
                fallbackText.disabled = true;
                fallbackText.removeAttribute("name");
            }
            if (detailsRow) detailsRow.hidden = false;
            if (detailsInput) detailsInput.required = false;
            hidden.disabled = false;
            syncHidden();
        }

        function syncHidden() {
            if (hidden.disabled) return;
            var w = wilayaSelect.value;
            var c = communeSelect.value;
            var d = detailsInput ? detailsInput.value.trim() : "";
            hidden.value = [w, c, d].filter(Boolean).join(" - ");
        }

        fetch("assets/data/algeria-cities.json")
            .then(function (res) {
                if (!res.ok) throw new Error("HTTP " + res.status);
                return res.json();
            })
            .then(function (data) {
                if (!Array.isArray(data) || !data.length) throw new Error("empty dataset");
                cities = data;

                // Unique wilayas, sorted by Arabic name.
                var seen = {};
                var wilayas = [];
                data.forEach(function (item) {
                    if (item && item.w && !seen[item.w]) {
                        seen[item.w] = true;
                        wilayas.push(item.w);
                    }
                });
                wilayas.sort(function (a, b) { return a.localeCompare(b, "ar"); });

                wilayas.forEach(function (w) {
                    var opt = document.createElement("option");
                    opt.value = w;
                    opt.textContent = w;
                    wilayaSelect.appendChild(opt);
                });

                enableLocationUI();
            })
            .catch(function () {
                // Leave the manual textarea as the fallback; nothing to do.
                if (fallbackRow) fallbackRow.hidden = false;
                if (fallbackText) {
                    fallbackText.required = true;
                    fallbackText.disabled = false;
                }
            });

        wilayaSelect.addEventListener("change", function () {
            var selected = this.value;
            communeSelect.innerHTML = '<option value="">اختر البلدية</option>';
            if (!selected) {
                if (communeRow) communeRow.hidden = true;
                syncHidden();
                return;
            }
            var seenC = {};
            cities
                .filter(function (item) { return item.w === selected; })
                .sort(function (a, b) { return a.c.localeCompare(b.c, "ar"); })
                .forEach(function (item) {
                    if (seenC[item.c]) return;
                    seenC[item.c] = true;
                    var opt = document.createElement("option");
                    opt.value = item.c;
                    opt.textContent = item.c;
                    communeSelect.appendChild(opt);
                });
            if (communeRow) communeRow.hidden = false;
            syncHidden();
        });

        communeSelect.addEventListener("change", syncHidden);
        if (detailsInput) detailsInput.addEventListener("input", syncHidden);

        // Ensure the hidden field is populated even if the user submits without
        // touching the last field (e.g. autofill / keyboard submit).
        var form = wilayaSelect.closest("form");
        if (form) form.addEventListener("submit", syncHidden);
    });
})();
