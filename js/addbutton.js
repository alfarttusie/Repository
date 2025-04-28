class ButtonCreator {
  static DEFAULT_MIN_COUNT = 1;

  static storedValues = [];

  static get Style() {
    return {
      mainDiv: {
        color: "whear",
        padding: "1%",
        width: "100%",
        height: "100%",
        overflow: "hidden",
      },
      header: {
        display: "flex",
        "justify-content": "space-between",
        "align-items": "center",
        padding: "1%",
        direction: "rtl",
        "border-bottom": "1px solid yellow",
        width: "100%",
        height: "10%",
        "border-radius": "8px",
        "background-color": "rgba(0, 0, 0, 0.384)",
      },
      headerLabel: {
        cursor: "pointer",
        "font-size": "clamp(10px, 3vw, 22px)",
        color: "whitesmoke",
        width: "10%",
        "max-width": "200px",
      },
      headerInput: {
        color: "white",
        padding: "0.3%",
        "border-left": "2px solid wheat",
        "border-right": "2px solid wheat",
        outline: "none",
        "background-color": "transparent",
        width: "40%",
      },
      counterLabel: { "font-size": "clamp(10px, 3vw, 22px)", color: "white" },
      counterinput: {
        color: "white",
        padding: "0.5%",
        "font-size": "clamp(10px, 3vw, 24px)",
        "font-weight": "bold",
        width: "clamp(10px, 4vw, 170px)",
        height: "100%",
        "border-bottom": "1px solid wheat",
        "text-align": "center",
        outline: "none",
        "-webkit-appearance": "none",
        "-moz-appearance": "textfield !important",
        appearance: "none",
        "background-color": "transparent",
      },
      linseHolder: {
        padding: "1%",
        "overflow-x": "hidden",
        "overflow-y": "auto",
        height: "100%",
      },
      line: {
        margin: "3% auto",
        padding: "1%",
        "max-height": "35px",
        "border-bottom": "1px solid skyblue",
      },
      inputField: {
        padding: "0.3%",
        color: "skyblue",
        "border-right": "1px solid wheat",
        "border-left": "1px solid wheat",
        outline: "none",
        width: "40%",
        "text-align": "center",
        "background-color": "#4b9e898a",
      },
    };
  }
  static initializeUI() {
    const HolderDiv = cleanWorkDiv("add-button");
    SetStyle(HolderDiv, ButtonCreator.Style["mainDiv"]);
    const buttonHolder = document.createElement("div");
    HolderDiv.appendChild(buttonHolder);

    const header = this.createHeader(buttonHolder);
    const inputCounter = header.querySelector(".counter-input");

    inputCounter.onkeydown = (event) =>
      this.handleArrowKeys(event, inputCounter);
    inputCounter.onwheel = (event) =>
      this.handleWheelEvent(event, inputCounter);
    inputCounter.oninput = () => this.handleManualInput(inputCounter);

    const linseHolder = document.createElement("div");
    linseHolder.classList.add("linseHolder");
    SetStyle(linseHolder, ButtonCreator.Style.linseHolder);
    buttonHolder.appendChild(linseHolder);

    const line = this.createLine();
    linseHolder.appendChild(line);
  }
  static createHeader(parent) {
    const header = document.createElement("div");
    SetStyle(header, ButtonCreator.Style.header);
    parent.appendChild(header);

    const label = Label(" الاسم : ", "button-name");
    SetStyle(label, ButtonCreator.Style.headerLabel);
    header.appendChild(label);

    const inputName = Input({
      type: "text",
      class: "button-name",
      id: "button-name",
    });
    SetStyle(inputName, ButtonCreator.Style.headerInput);
    header.appendChild(inputName);

    const counterLabel = Label(" عدد الجداول : ");
    counterLabel.classList.add("label-test");
    SetStyle(counterLabel, ButtonCreator.Style.counterLabel);
    header.appendChild(counterLabel);

    const inputCounter = Input({
      type: "number",
      class: "counter-input",
      value: this.DEFAULT_MIN_COUNT,
      min: this.DEFAULT_MIN_COUNT,
    });
    SetStyle(inputCounter, ButtonCreator.Style.counterinput);
    header.appendChild(inputCounter);

    header.appendChild(
      Button({
        innerText: "حفظ",
        class: "key-buttons",
        onclick: this.handleSaveButton,
      })
    );

    return header;
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
    const holder = document.querySelector(".linseHolder");
    let allLines = document.querySelectorAll(".addbuttonline");

    if (number > allLines.length) {
      console.log(`it's fine`);
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
    const line = lineCreator("addbuttonline");
    SetStyle(line, ButtonCreator.Style.line);

    line.appendChild(Label("نوع الحقل : "));

    const fieldTypeSelect = document.createElement("select");
    fieldTypeSelect.appendChild(createOption("small", "حقل عادي"));
    fieldTypeSelect.appendChild(createOption("big", "حقل كبير"));
    line.appendChild(fieldTypeSelect);

    const inputField = Input();
    if (index !== null && this.storedValues[index]) {
      inputField.value = this.storedValues[index];
    }
    SetStyle(inputField, ButtonCreator.Style.inputField);
    line.appendChild(inputField);

    line.appendChild(Label("مكانه الحقل : "));

    const fieldPositionSelect = document.createElement("select");
    fieldPositionSelect.appendChild(createOption("normal", "عادي"));
    fieldPositionSelect.appendChild(createOption("main", "رئيسي"));
    fieldPositionSelect.appendChild(createOption("password", "باسورد"));
    line.appendChild(fieldPositionSelect);

    return line;
  }
  static handleSaveButton() {
    const name = document.querySelector(".button-name");
    if (name.value.length < 1) {
      ShowMsg("اكتب اسم للزر");
      Shake(".button-name");
      return;
    }

    const allLines = document.querySelectorAll(".addbuttonline");
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
new ButtonCreator();
