/**
 * Custom Dropdown Styling
 * Converts standard select elements into beautifully styled dropdowns
 */

class CustomDropdown {
  constructor(selectElement) {
    this.select = selectElement;
    this.isOpen = false;
    this.init();
  }

  init() {
    // Hide the original select
    this.select.style.display = "none";
    this.select.classList.add("custom-select-hidden");

    // Create wrapper
    this.wrapper = document.createElement("div");
    this.wrapper.className = "custom-dropdown-wrapper";
    this.select.parentNode.insertBefore(this.wrapper, this.select);
    this.wrapper.appendChild(this.select);

    // Create button
    this.button = document.createElement("button");
    this.button.type = "button";
    this.button.className = "custom-dropdown-button";
    this.button.innerHTML =
      this.getSelectedText() +
      '<i data-lucide="chevron-down" class="custom-dropdown-icon"></i>';
    this.wrapper.appendChild(this.button);

    // Create dropdown menu
    this.menu = document.createElement("div");
    this.menu.className = "custom-dropdown-menu";
    this.wrapper.appendChild(this.menu);

    // Populate menu with options
    this.updateMenu();

    // Event listeners
    this.button.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      this.toggle();
    });

    this.select.addEventListener("change", () => {
      this.button.innerHTML =
        this.getSelectedText() +
        '<i data-lucide="chevron-down" class="custom-dropdown-icon"></i>';
      this.updateMenu();
      // Re-initialize Lucide icons
      if (typeof lucide !== "undefined") {
        lucide.createIcons();
      }
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", (e) => {
      if (!this.wrapper.contains(e.target)) {
        this.close();
      }
    });

    // Re-initialize Lucide icons
    if (typeof lucide !== "undefined") {
      lucide.createIcons();
    }
  }

  getSelectedText() {
    const selected = this.select.options[this.select.selectedIndex];
    return selected ? selected.text : "Select option";
  }

  updateMenu() {
    this.menu.innerHTML = "";
    const options = this.select.querySelectorAll("option");

    options.forEach((option, index) => {
      const item = document.createElement("div");
      item.className = "custom-dropdown-item";

      if (option.selected) {
        item.classList.add("selected");
      }

      if (option.value === "") {
        item.classList.add("placeholder");
      }

      item.textContent = option.text;
      item.dataset.value = option.value;
      item.dataset.index = index;

      item.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        this.select.selectedIndex = index;
        this.select.dispatchEvent(new Event("change", { bubbles: true }));
        this.close();
      });

      item.addEventListener("mouseenter", () => {
        this.menu
          .querySelectorAll(".custom-dropdown-item")
          .forEach((i) => i.classList.remove("hover"));
        item.classList.add("hover");
      });

      this.menu.appendChild(item);
    });
  }

  toggle() {
    this.isOpen ? this.close() : this.open();
  }

  open() {
    this.isOpen = true;
    this.button.classList.add("open");
    this.menu.classList.add("open");
    this.menu.style.display = "block";
  }

  close() {
    this.isOpen = false;
    this.button.classList.remove("open");
    this.menu.classList.remove("open");
    this.menu.style.display = "none";
  }
}

// Initialize all select elements with class 'custom-select'
document.addEventListener("DOMContentLoaded", () => {
  const selects = document.querySelectorAll(
    'select.custom-select, select[data-custom="true"]',
  );
  selects.forEach((select) => {
    new CustomDropdown(select);
  });
});

// Also provide a manual initialization function
window.initializeCustomDropdowns = function () {
  const selects = document.querySelectorAll(
    'select.custom-select, select[data-custom="true"]',
  );
  selects.forEach((select) => {
    if (!select.classList.contains("custom-select-hidden")) {
      new CustomDropdown(select);
    }
  });
};
