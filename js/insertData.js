class InsertData {
  constructor(name = null) {
    const menu = document.querySelector(".context-menu");
    const buttonName = name || (menu ? menu.dataset.invoker : null);
    if (findButtonByText(buttonName))
      Showindicator(findButtonByText(buttonName));

    if (buttonName) this.initialize(buttonName);
    else displayEmptyMessage(lang.get("no-button-selected"));
  }

  async initialize(buttonName) {
    const response = await sendRequest({
      type: "queries",
      job: "Get Columns",
      button: buttonName,
    });
    indicatorRemover();
    if (response.columns === "no columns") {
      this.showEmptyMessage(lang.get("no-columns"));
    } else {
      this.renderForm(response.columns, buttonName);
    }
  }

  showEmptyMessage(message) {
    const holder = home.WorkDiv;
    holder.innerHTML = "";

    elementCreator({
      type: "div",
      parent: holder,
      params: { className: "empty-message" },
      Children: [
        elementCreator({
          type: "span",
          params: { innerText: message },
        }),
      ],
    });
  }

  renderForm(columns, buttonName) {
    const holder = home.WorkDiv;
    holder.innerHTML = "";

    const container = elementCreator({
      type: "div",
      parent: holder,
      params: { className: "insertdata-container" },
    });

    const form = elementCreator({
      type: "form",
      parent: container,
      params: {
        className: "insertdata-form",
        onsubmit: (e) => {
          e.preventDefault();
          this.saveData(buttonName, form);
        },
      },
    });

    columns.forEach((column) => {
      const line = elementCreator({
        type: "div",
        parent: form,
        params: {
          className: "insertdata-line",
        },
      });

      line.appendChild(Label(column));
      line.appendChild(Input({ name: column }));
    });

    const footer = elementCreator({
      type: "div",
      parent: container,
      params: { className: "insertdata-footer" },
    });

    const saveButton = Button({
      class: "save-button key-buttons",
      innerText: lang.get("save-btn"),
      onclick: () => this.saveData(buttonName, form),
    });

    footer.appendChild(saveButton);

    container.onkeydown = (event) => {
      if (event.key === "Enter") saveButton.click();
    };
  }

  saveData(buttonName, form) {
    const data = {};

    form.querySelectorAll(".insertdata-line").forEach((line) => {
      const label = line.querySelector("label").innerText;
      const value = line.querySelector("input").value;
      data[label] = value;
    });
    Showindicator(document.querySelector(".insertdata-container"));
    sendRequest({
      type: "queries",
      job: "insert Data",
      button: buttonName,
      info: data,
    }).then(() => {
      indicatorRemover();
      new ShowButton(buttonName);
    });
  }
}
