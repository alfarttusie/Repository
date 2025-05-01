class ShowButton {
  constructor(event) {
    const buttonName = event.target ? event.target.innerText : event;
    Showindicator(findButtonByText(buttonName));
    this.loadData(buttonName);
  }

  async loadData(buttonName) {
    try {
      const response = await sendRequest({
        type: "queries",
        job: "show Button",
        button: buttonName,
      });

      indicatorRemover();

      if (response.response === "no data")
        return displayEmptyMessage("لا توجد معلومات");
      if (response.response === "no columns")
        return displayEmptyMessage("لا توجد أعمدة في هذا الزر");

      const holder = cleanWorkDiv("show-button");
      const header = this.createHeader(buttonName);
      holder.appendChild(header);

      response.data.forEach((entry) => {
        const line = this.createLine(entry, buttonName);
        holder.appendChild(line);
      });
    } catch (error) {
      console.error("فشل تحميل البيانات:", error);
      displayEmptyMessage("حدث خطأ أثناء تحميل البيانات");
    }
  }

  createHeader(buttonName) {
    const header = elementCreator({
      type: "div",
      params: { className: "showbutton-header" },
    });

    const searchInput = Input({
      type: "text",
      placeholder: "ابحث عن نص...",
      className: "showbutton-search",
    });

    header.appendChild(searchInput);

    elementCreator({
      parent: header,
      type: "label",
      params: {
        innerText: buttonName,
        className: "showbutton-label",
      },
    });

    return header;
  }

  createLine(data, buttonName) {
    const line = elementCreator({
      type: "div",
      params: { className: "showbutton-line" },
    });

    const mainContent = this.createMainFields(data.main || {});
    const passwordContent = this.createPasswordFields(data.passwords || {});

    line.appendChild(mainContent);
    line.appendChild(passwordContent);

    const detailsButton = elementCreator({
      type: "button",
      params: {
        innerText: "تفاصيل",
        className: "showbutton-details-btn",
        onclick: () => this.showId(data.id, buttonName),
      },
    });

    line.appendChild(detailsButton);

    return line;
  }

  createMainFields(fields) {
    const container = elementCreator({
      type: "div",
      params: { className: "showbutton-main" },
    });

    Object.entries(fields).forEach(([key, value]) => {
      const field = elementCreator({
        type: "div",
        params: { className: "showbutton-main-item" },
      });

      field.appendChild(Label(key));

      const span = Span({ innerText: value, ondblclick: copyToClipboard });
      span.classList.add("showbutton-main-span");
      field.appendChild(span);

      container.appendChild(field);
    });

    return container;
  }

  createPasswordFields(fields) {
    const container = elementCreator({
      type: "div",
      params: { className: "showbutton-password" },
    });

    Object.entries(fields).forEach(([key, value]) => {
      const field = elementCreator({
        type: "div",
        params: { className: "showbutton-password-item" },
      });

      field.appendChild(Label(key));
      const passField = PasswordField({ value });
      passField.classList.add("showbutton-password-field");
      field.appendChild(passField);

      container.appendChild(field);
    });

    return container;
  }

  async showId(id, buttonName) {
    try {
      const callback = await sendRequest({
        type: "queries",
        job: "select id",
        button: buttonName,
        id: id,
      });

      const holder = home.WorkDiv;
      holder.innerHTML = "";

      Object.entries(callback.data).forEach(([key, value]) => {
        if (key === "id") return;

        const line = lineCreator("showbutton-detail");
        line.appendChild(Label(key));
        line.appendChild(Span({ innerText: value }));
        holder.appendChild(line);
      });
    } catch (error) {
      console.error("فشل تحميل التفاصيل:", error);
      displayEmptyMessage("حدث خطأ أثناء تحميل التفاصيل");
    }
  }
}
