class InsertData {
  constructor(name = null) {
    const menu = document.querySelector(".context-menu");
    const buttonName = name ? name : menu.dataset.invoker;

    this.initialize(buttonName);
  }

  async initialize(buttonName) {
    const response = await sendRequest({
      type: "queries",
      job: "Get Columns",
      button: buttonName,
    });

    if (response.columns === "no columns") {
      this.showEmptyMessage("لاتوجد أعمدة في هذا الزر");
    } else {
      this.renderColumns(response.columns, buttonName);
    }
  }

  showEmptyMessage(message) {
    const holderDiv = home.WorkDiv;
    holderDiv.innerHTML = "";

    const msgDiv = document.createElement("div");
    msgDiv.classList.add("empty");

    const span = document.createElement("span");
    span.innerText = message;

    msgDiv.appendChild(span);
    holderDiv.appendChild(msgDiv);
  }

  renderColumns(columns, buttonName) {
    const leftDiv = document.querySelector(".left");
    leftDiv.innerHTML = "";

    const holderDiv = document.createElement("div");
    holderDiv.className = "InsertData-div";
    leftDiv.appendChild(holderDiv);

    const linesHolder = document.createElement("div");
    linesHolder.classList.add("insertdata-linesHolder");
    columns.forEach((item) => {
      const line = lineCreator("insertdata-line");

      const label = document.createElement("label");
      label.className = "label";
      label.innerText = item;

      const input = document.createElement("input");

      line.appendChild(label);
      line.appendChild(input);
      linesHolder.appendChild(line);
    });
    const footer = document.createElement("div");
    footer.classList.add("footer");

    holderDiv.appendChild(linesHolder);
    holderDiv.appendChild(footer);
    const saveButton = Button({
      class: "save-button",
      innerText: "حفظ",
      class: "key-buttons",
      onclick: () => this.saveData(buttonName, linesHolder),
    });

    footer.appendChild(saveButton);
    holderDiv.onkeydown = (event) => {
      if (event.key == "Enter") saveButton.click();
    };
  }

  saveData(buttonName, holderDiv) {
    const data = {};
    holderDiv.querySelectorAll(".line").forEach((line) => {
      const label = line.querySelector("label").innerText;
      const value = line.querySelector("input").value;
      data[label] = value;
    });

    sendRequest({
      type: "queries",
      job: "insert Data",
      button: buttonName,
      info: data,
    }).then(() => new ShowButton(buttonName));
  }
}
