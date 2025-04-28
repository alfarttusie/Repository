class ButtonSettings {
  WorkDiv = null;

  constructor(Name = null) {
    const menu = document.querySelector(".context-menu");
    const buttonName = Name || menu?.dataset?.invoker;

    if (!buttonName) {
      console.error("اسم الزر غير متوفر.");
      showNotification("تعذر العثور على اسم الزر.");
      return;
    }

    this.WorkDiv = home?.WorkDiv || null;

    if (!this.WorkDiv) {
      console.error("WorkDiv غير متوفر.");
      showNotification("تعذر العثور على واجهة العمل.");
      return;
    }

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
        showNotification("لا توجد حقول مرتبطة بالزر.");
      }

      const addNewButton = this.createElement("button", {
        innerText: "إضافة",
        className: "add-new-btn",
        onclick: () => {
          const line = this.createNewLine(buttonName);
          this.WorkDiv.appendChild(line);
        },
      });

      this.WorkDiv.appendChild(addNewButton);
    } catch (error) {
      console.error("خطأ أثناء تحميل إعدادات الزر:", error);
      showNotification("حدث خطأ أثناء تحميل الإعدادات. حاول مرة أخرى.");
    }
  }

  createLine(columnName, columnType, buttonName) {
    const line = this.createElement("div", { className: "line" });

    const input = this.createElement("input", {
      value: columnName,
      readOnly: true,
      className: "column-input",
    });

    const typeSelect = this.createTypeSelect(columnType);
    const saveTypeButton = this.createElement("button", {
      innerText: "حفظ النوع",
      className: "save-type-btn",
      onclick: () => {
        this.updateColumnType(buttonName, columnName, typeSelect.value);
      },
    });

    const deleteButton = this.createElement("button", {
      innerText: "حذف",
      className: "delete-btn",
      onclick: () => {
        this.deleteColumn(buttonName, columnName, line);
      },
    });

    line.append(input, typeSelect, saveTypeButton, deleteButton);

    return line;
  }

  createTypeSelect(selectedType) {
    const typeSelect = this.createElement("select", {
      className: "type-select",
    });

    const types = [
      { value: "main", text: "رئيسي" },
      { value: "password", text: "باسورد" },
      { value: "normal", text: "عادي" },
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
    const line = this.createElement("div", { className: "line" });

    const nameInput = this.createElement("input", {
      placeholder: "اسم الحقل",
      className: "new-column-input",
    });

    const typeSelect = this.createTypeSelect("main");

    const saveButton = this.createElement("button", {
      innerText: "حفظ",
      className: "save-btn",
      onclick: () => {
        const columnName = nameInput.value.trim();
        const columnType = typeSelect.value;

        if (!columnName) {
          showNotification("يرجى إدخال اسم صالح للحقل.");
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
        showNotification("تمت الإضافة بنجاح!");
        line.remove();
        const newLine = this.createLine(columnName, columnType, buttonName);
        this.WorkDiv.appendChild(newLine);
      } else {
        showNotification("فشل في إضافة الحقل.");
      }
    } catch (error) {
      console.error("خطأ أثناء إضافة الحقل:", error);
      showNotification("حدث خطأ أثناء الإضافة. حاول مرة أخرى.");
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
        showNotification("تم حذف الحقل بنجاح!");
        lineElement.remove();
      } else {
        showNotification("فشل في حذف الحقل.");
      }
    } catch (error) {
      console.error("خطأ أثناء حذف الحقل:", error);
      showNotification("حدث خطأ أثناء الحذف. حاول مرة أخرى.");
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
        showNotification("تم تغيير نوع الحقل بنجاح!");
      } else {
        showNotification("فشل في تغيير نوع الحقل.");
      }
    } catch (error) {
      console.error("خطأ أثناء تغيير نوع الحقل:", error);
      showNotification("حدث خطأ أثناء التغيير. حاول مرة أخرى.");
    }
  }

  createElement(tag, options = {}, children = []) {
    const element = document.createElement(tag);
    Object.assign(element, options);
    if (Array.isArray(children))
      children.forEach((child) => element.appendChild(child));
    return element;
  }
}
