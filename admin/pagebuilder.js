document.addEventListener("DOMContentLoaded", () => {
  const builder = document.getElementById("page-builder");

  // Remove block
  builder.addEventListener("click", function (e) {
    if (e.target.classList.contains("remove-block")) {
      e.target.closest(".block").remove();
    }
  });
});

// Add block dynamically
function addBlock(type) {
  const builder = document.getElementById("page-builder");
  const index = builder.querySelectorAll(".block").length;

  let html = `<div class="block" data-type="${type}">`;
  html += `<input type="hidden" name="blocks[${index}][type]" value="${type}">`;

  switch (type) {
    case "hero":
      html += `
        <div class="hero-block">
          <label>Hero Image URL:</label>
          <input type="text" name="blocks[${index}][image]" value="">

          <label>Custom HTML:</label>
          <textarea name="blocks[${index}][html]"></textarea>

          <label>Custom CSS:</label>
          <textarea name="blocks[${index}][css]"></textarea>

          <label>Block Class:</label>
          <input type="text" name="blocks[${index}][class]">
        </div>`;
      break;

    case "text":
      html += `
        <div class="text-block">
          <label>Text Content:</label>
          <textarea name="blocks[${index}][content]"></textarea>

          <label>Custom CSS:</label>
          <textarea name="blocks[${index}][css]"></textarea>

          <label>Block Class:</label>
          <input type="text" name="blocks[${index}][class]">
        </div>`;
      break;

    case "image":
      html += `
        <div class="image-block">
          <label>Image URL:</label>
          <input type="text" name="blocks[${index}][src]">

          <label>Caption:</label>
          <input type="text" name="blocks[${index}][caption]">

          <label>Custom CSS:</label>
          <textarea name="blocks[${index}][css]"></textarea>

          <label>Block Class:</label>
          <input type="text" name="blocks[${index}][class]">
        </div>`;
      break;

    case "code":
      html += `
        <div class="code-block">
          <label>Code Snippet:</label>
          <textarea name="blocks[${index}][code]"></textarea>

          <label>Custom CSS:</label>
          <textarea name="blocks[${index}][css]"></textarea>

          <label>Block Class:</label>
          <input type="text" name="blocks[${index}][class]">
        </div>`;
      break;
  }

  html += `<button class="remove-block">🗑️ Remove Block</button>`;
  html += `</div>`;

  builder.insertAdjacentHTML("beforeend", html);
}

// Prepare JSON for saving
function prepareSave() {
  const blocks = [];
  const blockElements = document.querySelectorAll("#page-builder .block");

  blockElements.forEach((block) => {
    const type = block.dataset.type;
    const data = { type };

    switch (type) {
      case "hero":
        data.image = block.querySelector(`[name*="[image]"]`).value;
        data.html = block.querySelector(`[name*="[html]"]`).value;
        data.css = block.querySelector(`[name*="[css]"]`).value;
        data.class = block.querySelector(`[name*="[class]"]`).value;
        break;

      case "text":
        data.content = block.querySelector(`[name*="[content]"]`).value;
        data.css = block.querySelector(`[name*="[css]"]`).value;
        data.class = block.querySelector(`[name*="[class]"]`).value;
        break;

      case "image":
        data.src = block.querySelector(`[name*="[src]"]`).value;
        data.caption = block.querySelector(`[name*="[caption]"]`).value;
        data.css = block.querySelector(`[name*="[css]"]`).value;
        data.class = block.querySelector(`[name*="[class]"]`).value;
        break;

      case "code":
        data.code = block.querySelector(`[name*="[code]"]`).value;
        data.css = block.querySelector(`[name*="[css]"]`).value;
        data.class = block.querySelector(`[name*="[class]"]`).value;
        break;
    }

    blocks.push(data);
  });

  document.getElementById("page-data").value = JSON.stringify(blocks);
  return true;
}