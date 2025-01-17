class ButtonCreator {
  static DEFAULT_MIN_COUNT = 1;

  static storedValues = [];

  static initializeUI() {
    const leftDiv = document.querySelector(".left");
    leftDiv.innerHTML = "";

    const buttonHolder = document.createElement("div");
    buttonHolder.className = "addbutton-holder";
    leftDiv.appendChild(buttonHolder);

    const header = this.createHeader(buttonHolder);
    const inputCounter = header.querySelector(".counter-input");

    inputCounter.onkeydown = (event) =>
      this.handleArrowKeys(event, inputCounter);
    inputCounter.onwheel = (event) =>
      this.handleWheelEvent(event, inputCounter);
    inputCounter.oninput = () => this.handleManualInput(inputCounter);

    const line = this.createLine();
    buttonHolder.appendChild(line);
  }

  static createHeader(parent) {
    const header = document.createElement("div");
    header.className = "addbutton-header";
    parent.appendChild(header);

    header.appendChild(this.createLabel(" الاسم : ", "button-name"));

    const inputName = document.createElement("input");
    inputName.className = "button-name";
    inputName.id = "button-name";
    header.appendChild(inputName);

    header.appendChild(this.createLabel(" عدد الجداول : "));

    const inputCounter = document.createElement("input");
    inputCounter.type = "number";
    inputCounter.className = "counter-input";
    inputCounter.value = this.DEFAULT_MIN_COUNT;
    inputCounter.min = this.DEFAULT_MIN_COUNT;
    header.appendChild(inputCounter);

    const saveButton = document.createElement("button");
    saveButton.innerText = "حفظ";
    saveButton.onclick = this.handleSaveButton;
    header.appendChild(saveButton);

    return header;
  }

  static createLabel(text, htmlFor = "") {
    const label = document.createElement("label");
    label.innerText = text;
    if (htmlFor) label.htmlFor = htmlFor;
    return label;
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
    if (event.deltaY < 0) {
      value += 1;
    } else if (event.deltaY > 0 && value > this.DEFAULT_MIN_COUNT) {
      value -= 1;
    }
    inputCounter.value = value;
    this.updateLines(value);
  }

  static handleManualInput(inputCounter) {
    let value = parseInt(inputCounter.value);
    if (isNaN(value) || value < this.DEFAULT_MIN_COUNT) {
      value = this.DEFAULT_MIN_COUNT;
    }
    inputCounter.value = value;
    this.updateLines(value);
  }

  static updateLines(number) {
    const holder = document.querySelector(".addbutton-holder");
    let allLines = document.querySelectorAll(".button-line");

    if (number > allLines.length) {
      for (let i = allLines.length; i < number; i++) {
        const line = this.createLine(i);
        holder.appendChild(line);
      }
    } else if (number < allLines.length) {
      for (let i = allLines.length; i > number; i--) {
        const lineIndex = i - 1;
        const inputField = allLines[lineIndex].querySelector("input");
        this.storedValues[lineIndex] = inputField.value;
        allLines[lineIndex].remove();
      }
    }
  }

  static createLine(index = null) {
    const line = document.createElement("div");
    line.className = "line button-line";

    const fieldTypeLabel = this.createLabel("نوع الحقل");
    line.appendChild(fieldTypeLabel);

    const fieldTypeSelect = document.createElement("select");
    fieldTypeSelect.appendChild(this.createOption("small", "حقل عادي"));
    fieldTypeSelect.appendChild(this.createOption("big", "حقل كبير"));
    line.appendChild(fieldTypeSelect);

    const inputField = document.createElement("input");
    if (index !== null && this.storedValues[index]) {
      inputField.value = this.storedValues[index];
    }
    line.appendChild(inputField);

    const fieldPositionLabel = this.createLabel("مكانه الحقل");
    line.appendChild(fieldPositionLabel);

    const fieldPositionSelect = document.createElement("select");
    fieldPositionSelect.appendChild(this.createOption("main", "رئيسي"));
    fieldPositionSelect.appendChild(this.createOption("password", "باسورد"));
    fieldPositionSelect.appendChild(this.createOption("normal", "عادي"));
    line.appendChild(fieldPositionSelect);

    return line;
  }

  static createOption(value, innerText) {
    const option = document.createElement("option");
    option.value = value;
    option.innerText = innerText;
    return option;
  }

  static handleSaveButton() {
    const name = document.querySelector(".button-name");
    if (name.value.length < 1) {
      ShowMsg("اكتب اسم للزر");
      Shake(".button-name");
      return;
    }

    const allLines = document.querySelectorAll(".button-line");
    const main = [];
    const passwords = [];
    const others = [];

    allLines.forEach((line) => {
      const column = line.children[2].value;
      const type = line.children[4].value;

      if (column.length > 0) {
        if (type === "main") main.push(column);
        else if (type === "password") passwords.push(column);
        else others.push(column);
      }
    });

    sendRequest({
      type: "queries",
      job: "new button",
      button: name.value,
      main,
      password: passwords,
      columns: [...main, ...passwords, ...others],
    }).then((callback) => {
      if (callback.response === "invalid key") {
        showNotification("لا يوجد مفتاح تشفير");
      } else if (callback.response === "Button exist") {
        showNotification("الاسم موجود مسبقا");
      } else if (callback.response === "successful") {
        showNotification("تم الإضافة بنجاح");
        home.GetButtons();
        new InsertData(name.value);
      }
    });
  }

  constructor() {
    document.querySelector("#addButton").onclick =
      ButtonCreator.initializeUI.bind(ButtonCreator);
  }
}
