class ButtonCreator {
  static DEFAULT_MIN_COUNT = 1;

  static initializeUI() {
    const HolderDiv = cleanWorkDiv("add-button");

    const container = elementCreator({
      parent: HolderDiv,
      type: "div",
      params: { className: "buttonCreatorContainer" },
    });

    ButtonCreator.createHeader(container);
    ButtonCreator.createLinesHolder(container);
    ButtonCreator.updateLines(ButtonCreator.DEFAULT_MIN_COUNT);
  }

  static createHeader(parent) {
    const header = elementCreator({
      parent,
      type: "div",
      params: { className: "add-button-header" },
    });

    // Label
    elementCreator({
      parent: header,
      type: "label",
      params: {
        htmlFor: "button-name",
        innerText: lang.get("button-name"),
        className: "header-label",
      },
    });

    // Input
    elementCreator({
      parent: header,
      type: "input",
      params: {
        type: "text",
        id: "button-name",
        className: "button-name header-input",
      },
    });

    // Counter label
    elementCreator({
      parent: header,
      type: "label",
      params: {
        innerText: lang.get("number-of-fields"),
        className: "header-label",
      },
    });

    // Counter input
    const counter = elementCreator({
      parent: header,
      type: "input",
      params: {
        type: "number",
        min: this.DEFAULT_MIN_COUNT,
        value: this.DEFAULT_MIN_COUNT,
        className: "counter-input",
      },
    });

    counter.addEventListener("keydown", (e) =>
      this.handleArrowKeys(e, counter)
    );
    counter.addEventListener("wheel", (e) => this.handleWheelEvent(e, counter));
    counter.addEventListener("input", () => this.handleManualInput(counter));

    // Save button
    elementCreator({
      parent: header,
      type: "button",
      params: {
        innerText: lang.get("save-btn"),
        className: "key-buttons",
        onclick: this.handleSaveButton.bind(this),
      },
    });
  }

  static createLinesHolder(parent) {
    elementCreator({
      parent,
      type: "div",
      params: {
        className: "linseHolder",
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
    const holder = document.querySelector(".linseHolder");
    const allLines = document.querySelectorAll(".addbuttonline");

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
      params: { className: "addbuttonline line" },
    });

    elementCreator({
      parent: line,
      type: "label",
      params: { innerText: lang.get("field-type") },
    });

    const typeSelect = elementCreator({
      parent: line,
      type: "select",
      params: { className: "field-type" },
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
        className: "line-input",
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
      params: { className: "field-position" },
    });
    posSelect.appendChild(createOption("normal", "عادي"));
    posSelect.appendChild(createOption("main", "رئيسي"));
    posSelect.appendChild(createOption("password", "باسورد"));

    return line;
  }

  static handleSaveButton() {
    const name = document.querySelector(".button-name").value.trim();

    if (name.length === 0) {
      showNotification("اكتب اسم للزر");
      Shake(".button-name");
      return;
    }

    const lines = document.querySelectorAll(".addbuttonline");
    const main = [],
      passwords = [],
      others = [];

    lines.forEach((line) => {
      const input = line.querySelector(".line-input")?.value.trim();
      const type = line.querySelector(".field-position").value;
      if (!input) return;

      if (type === "main") main.push(input);
      else if (type === "password") passwords.push(input);
      else others.push(input);
    });
    Showindicator(document.querySelector(".add-button-header"));
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
        showNotification("لا يوجد مفتاح تشفير");
      } else if (res.response === "Button exist") {
        showNotification("الاسم موجود مسبقا");
      } else if (res.response === "successful") {
        showNotification("تم الإضافة بنجاح");
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
