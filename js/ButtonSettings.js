class ButtonSettings {
  WorkDiv = null;

  constructor(Name = null) {
    const menu = document.querySelector(".context-menu");
    const buttonName = Name || menu?.dataset?.invoker;

    if (!buttonName) {
      showNotification(lang.get("button-nofound"));
      return;
    }

    this.WorkDiv = home?.WorkDiv || null;
    this.loadButtonSettings(buttonName);
  }

  async loadButtonSettings(buttonName) {
    try {
      const callback = await sendRequest({
        type: "queries",
        job: "Button Columns Type",
        button: buttonName,
      });

      this.WorkDiv.innerHTML = "";

      if (callback?.columns && callback.columns !== "empty") {
        Object.keys(callback.columns).forEach((column) => {
          const line = this.createLine(
            column,
            callback.columns[column],
            buttonName
          );
          this.WorkDiv.appendChild(line);
        });
      } else {
        showNotification(lang.get("no-fields"));
      }

      const addNewButton = this.createElement("button", {
        innerText: lang.get("add"),
        className: "bs-add-new-btn",
        onclick: () => {
          const line = this.createNewLine(buttonName);
          this.WorkDiv.appendChild(line);
        },
      });

      this.WorkDiv.appendChild(addNewButton);
    } catch (error) {
      console.error(error);
      showNotification(lang.get("error-loading-settings"));
    }
  }

  createLine(columnName, columnType, buttonName) {
    const line = this.createElement("div", { className: "bs-line" });
    const renameBtn = this.createElement("button", {
      innerText: lang.get("rename"),
      className: "rename-column-btn",
      onclick: () => {
        const newName = prompt(lang.get("new-name"), columnName);
        if (!newName || newName === columnName) return;

        this.renameColumn(buttonName, columnName, newName, line);
      },
    });

    const input = this.createElement("input", {
      value: columnName,
      readOnly: true,
      className: "bs-column-input",
    });

    const typeSelect = this.createTypeSelect(columnType);

    const saveTypeButton = this.createElement("button", {
      innerText: lang.get("save-kind"),
      className: "bs-save-type-btn",
      onclick: () => {
        this.updateColumnType(buttonName, columnName, typeSelect.value);
      },
    });

    const deleteButton = this.createElement("button", {
      innerText: lang.get("delete"),
      className: "bs-delete-btn",
      onclick: () => {
        if (confirm(lang.get("delete-column-confirm")))
          this.deleteColumn(buttonName, columnName, line);
      },
    });

    line.append(input, typeSelect, saveTypeButton, deleteButton, renameBtn);
    return line;
  }

  createTypeSelect(selectedType) {
    const typeSelect = this.createElement("select", {
      className: "bs-type-select",
    });

    const types = [
      { value: "main", text: lang.get("main-field") },
      { value: "password", text: lang.get("password-field") },
      { value: "normal", text: lang.get("normal-field") },
    ];

    types.forEach(({ value, text }) => {
      const option = this.createElement("option", {
        value,
        innerText: text,
        selected: value === selectedType,
      });
      typeSelect.appendChild(option);
    });

    return typeSelect;
  }

  createNewLine(buttonName) {
    const line = this.createElement("div", { className: "bs-line" });

    const nameInput = this.createElement("input", {
      placeholder: lang.get("enter-field-name"),
      className: "bs-new-column-input",
      onkeydown: (e) => Clicker(e, saveButton),
    });

    const typeSelect = this.createTypeSelect("main");

    const saveButton = this.createElement("button", {
      innerText: lang.get("save-btn"),
      className: "bs-save-btn",
      onclick: () => {
        const columnName = nameInput.value.trim();
        const columnType = typeSelect.value;

        if (!columnName) {
          showNotification(lang.get("enter-field-name"));
          return;
        }

        this.addColumn(buttonName, columnName, columnType, line);
      },
    });

    line.append(typeSelect, nameInput, saveButton);
    return line;
  }

  async addColumn(buttonName, columnName, columnType, line) {
    try {
      const response = await sendRequest({
        type: "queries",
        job: "New Column",
        button: buttonName,
        column: columnName,
        FieldType: columnType,
      });

      if (response.status === "successful") {
        showNotification(lang.get("added-successfully"));
        new ButtonSettings(buttonName);
      } else {
        showNotification(lang.get("failed-to-add"));
      }
    } catch (error) {
      console.error(error);
    }
  }

  async deleteColumn(buttonName, columnName, lineElement) {
    try {
      const response = await sendRequest({
        type: "queries",
        job: "Delete Column",
        button: buttonName,
        column: columnName,
      });

      if (response.status === "successful") {
        showNotification(lang.get("deleted-successfully"));
        lineElement.remove();
      } else {
        showNotification(lang.get("failed-to-delete"));
      }
    } catch (error) {
      console.error(error);
    }
  }

  async updateColumnType(buttonName, columnName, columnType) {
    try {
      const response = await sendRequest({
        type: "queries",
        job: "change type",
        button: buttonName,
        column: columnName,
        FieldType: columnType,
      });

      if (response.status === "successful") {
        showNotification(lang.get("changed-successfully"));
      } else {
        showNotification(lang.get("failed-to-change"));
      }
    } catch (error) {
      console.error(error);
    }
  }

  createElement(tag, options = {}, children = []) {
    const element = document.createElement(tag);
    Object.assign(element, options);
    if (Array.isArray(children))
      children.forEach((child) => element.appendChild(child));
    return element;
  }
  async renameColumn(buttonName, oldName, newName, lineElement) {
    try {
      const response = await sendRequest({
        type: "queries",
        job: "Rename Column",
        button: buttonName,
        column: oldName,
        new: newName,
      });

      if (response.status === "successful") {
        showNotification(lang.get("rename-success"));
        new ButtonSettings(buttonName);
      } else {
        showNotification(lang.get("failed-to-rename"));
      }
    } catch (error) {
      console.error(error);
      showNotification(lang.get("error-loading-settings"));
    }
  }
}
