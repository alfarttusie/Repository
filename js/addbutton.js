class ButtonCreator {
  static DEFAULT_MIN_COUNT = 1;

  static initializeUI() {
    const HolderDiv = cleanWorkDiv("btn-creator-wrapper");
  
    const container = elementCreator({
      parent: HolderDiv,
      type: "div",
      params: { className: "buttonCreatorContainer" },
    });
  
    ButtonCreator.createHeader(container);
  
    const scrollWrapper = elementCreator({
      parent: container,
      type: "div",
      params: { className: "creator-scroll-area" },
    });
  
    ButtonCreator.createLinesHolder(scrollWrapper);
    ButtonCreator.updateLines(ButtonCreator.DEFAULT_MIN_COUNT);
  }
  

  static createHeader(parent) {
    const header = elementCreator({
      parent,
      type: "div",
      params: { className: "btn-creator-header" },
    });

    elementCreator({
      parent: header,
      type: "label",
      params: {
        htmlFor: "btn-creator-name",
        innerText: lang.get("button-name"),
        className: "btn-creator-label",
      },
    });

    elementCreator({
      parent: header,
      type: "input",
      params: {
        type: "text",
        id: "btn-creator-name",
        className: "btn-creator-name btn-creator-input",
      },
    });

    elementCreator({
      parent: header,
      type: "label",
      params: {
        innerText: lang.get("number-of-fields"),
        className: "btn-creator-label",
      },
    });

    const counter = elementCreator({
      parent: header,
      type: "input",
      params: {
        type: "number",
        min: this.DEFAULT_MIN_COUNT,
        value: this.DEFAULT_MIN_COUNT,
        className: "btn-creator-counter",
      },
    });

    counter.addEventListener("keydown", (e) =>
      this.handleArrowKeys(e, counter)
    );
    counter.addEventListener("wheel", (e) => this.handleWheelEvent(e, counter));
    counter.addEventListener("input", () => this.handleManualInput(counter));

    elementCreator({
      parent: header,
      type: "button",
      params: {
        innerText: lang.get("save-btn"),
        className: "btn-creator-save",
        onclick: this.handleSaveButton.bind(this),
      },
    });
  }

  static createLinesHolder(parent) {
    elementCreator({
      parent,
      type: "div",
      params: {
        className: "btn-creator-lines",
      },
    });
  }

  static handleArrowKeys(event, inputCounter) {
    let value = parseInt(inputCounter.value);
    if (event.key === "ArrowUp") {
      event.preventDefault();
      inputCounter.value = value + 1;
      this.updateLines(value + 1);
    } else if (event.key === "ArrowDown" && value > this.DEFAULT_MIN_COUNT) {
      event.preventDefault();
      inputCounter.value = value - 1;
      this.updateLines(value - 1);
    }
  }

  static handleWheelEvent(event, inputCounter) {
    let value = parseInt(inputCounter.value);
    if (event.deltaY < 0) value++;
    else if (event.deltaY > 0 && value > this.DEFAULT_MIN_COUNT) value--;

    inputCounter.value = value;
    this.updateLines(value);
  }

  static handleManualInput(inputCounter) {
    let value = parseInt(inputCounter.value);
    if (isNaN(value) || value < this.DEFAULT_MIN_COUNT)
      value = this.DEFAULT_MIN_COUNT;

    inputCounter.value = value;
    this.updateLines(value);
  }

  static updateLines(count) {
    const holder = document.querySelector(".btn-creator-lines");
    const allLines = document.querySelectorAll(".btn-creator-line");

    if (count > allLines.length) {
      for (let i = allLines.length; i < count; i++) {
        const line = this.createLine();
        holder.appendChild(line);
      }
    } else {
      for (let i = allLines.length - 1; i >= count; i--) {
        allLines[i].remove();
      }
    }
  }

  static createLine() {
    const line = elementCreator({
      type: "div",
      parent: null,
      params: { className: "btn-creator-line" },
    });

    elementCreator({
      parent: line,
      type: "label",
      params: { innerText: lang.get("field-type") },
    });

    const typeSelect = elementCreator({
      parent: line,
      type: "select",
      params: { className: "btn-creator-type" },
    });
    ["small", "big"].forEach((type) => {
      typeSelect.appendChild(
        createOption(
          type,
          type === "small" ? lang.get("normal-field") : lang.get("big-field")
        )
      );
    });

    elementCreator({
      parent: line,
      type: "input",
      params: {
        type: "text",
        className: "btn-creator-line-input",
      },
    });

    elementCreator({
      parent: line,
      type: "label",
      params: { innerText: lang.get("field-position") },
    });

    const posSelect = elementCreator({
      parent: line,
      type: "select",
      params: { className: "btn-creator-position" },
    });
    posSelect.appendChild(createOption("normal", lang.get("normal-field")));
    posSelect.appendChild(createOption("main", lang.get("main-field")));
    posSelect.appendChild(createOption("password", lang.get("password-field")));

    return line;
  }

  static handleSaveButton() {
    const name = document.querySelector(".btn-creator-name").value.trim();

    if (name.length === 0) {
      showNotification(lang.get("empty-button-name"));
      Shake(".btn-creator-name");
      return;
    }

    const lines = document.querySelectorAll(".btn-creator-line");
    const main = [],
      passwords = [],
      others = [];

    lines.forEach((line) => {
      const input = line.querySelector(".btn-creator-line-input")?.value.trim();
      const type = line.querySelector(".btn-creator-position").value;
      if (!input) return;

      if (type === "main") main.push(input);
      else if (type === "password") passwords.push(input);
      else others.push(input);
    });

    Showindicator(document.querySelector(".btn-creator-wrapper"));
    sendRequest({
      type: "queries",
      job: "new button",
      button: name,
      main,
      password: passwords,
      columns: [...main, ...passwords, ...others],
    }).then((res) => {
      indicatorRemover();
      if (res.response === "invalid key") {
        showNotification(lang.get("no-encryptation-key"));
      } else if (res.response === "Button exist") {
        showNotification("الاسم موجود مسبقا");
      } else if (res.response === "successful") {
        showNotification(lang.get("added-successfully"));
        home.GetButtons();
        new InsertData(name);
      }
    });
  }

  constructor() {
    const trigger = document.querySelector("#addButton");
    if (trigger) trigger.onclick = ButtonCreator.initializeUI.bind(this);
  }
}

new ButtonCreator();
