class ShowButton {
  constructor(event) {
    const buttonName = event.target ? event.target.innerText : event;
    indicatorRemover();
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
        return displayEmptyMessage(lang.get("no-info"));
      if (response.response === "no columns")
        return displayEmptyMessage(lang.get("no-columns"));

      const holder = cleanWorkDiv("show-button");
      const header = this.createHeader(buttonName);
      const listContainer = document.createElement("div");
      listContainer.className = "sb-list";
      holder.appendChild(header);
      holder.appendChild(listContainer);

      response.data.forEach((entry) => {
        const line = this.createLine(entry, buttonName);
        listContainer.appendChild(line);
      });

      const input = header.querySelector(".sb-search");
      input.oninput = (e) => {
        const val = e.target.value.toLowerCase();
        listContainer.querySelectorAll(".sb-line").forEach((line) => {
          line.style.display = line.innerText.toLowerCase().includes(val)
            ? "flex"
            : "none";
        });
      };
    } catch (error) {
      console.error(error);
      displayEmptyMessage(lang.get("loading-error"));
    }
  }

  createHeader(buttonName) {
    const header = elementCreator({
      type: "div",
      params: { className: "sb-header" },
    });

    const searchInput = Input({
      type: "text",
      placeholder: lang.get("search"),
      className: "sb-search",
      autocomplete: "off",
    });

    header.appendChild(searchInput);

    elementCreator({
      parent: header,
      type: "label",
      params: {
        innerText: buttonName,
        className: "sb-label",
      },
    });

    return header;
  }

  createLine(data, buttonName) {
    const line = elementCreator({
      type: "div",
      params: { className: "sb-line" },
    });

    const detailsButton = elementCreator({
      type: "button",
      params: {
        innerText: lang.get("show"),
        className: "sb-details-btn",
        onclick: () => this.showId(data.id, buttonName, line),
      },
    });
    line.appendChild(detailsButton);

    const mainContent = this.createMainFields(data.main || {});
    const passwordContent = this.createPasswordFields(data.passwords || {});

    line.appendChild(mainContent);
    line.appendChild(passwordContent);

    return line;
  }

  createMainFields(fields) {
    const container = elementCreator({
      type: "div",
      params: { className: "sb-main" },
    });

    if (fields !== "empty") {
      Object.entries(fields).forEach(([key, value]) => {
        const field = elementCreator({
          type: "div",
          params: { className: "sb-main-item" },
        });

        const isEmpty = value === "empty" || value === "";

        const span = Span({
          innerText: isEmpty ? lang.get("no-data") : value,
          ondblclick: !isEmpty ? copyToClipboard : null,
        });

        span.classList.add("sb-main-span");
        if (isEmpty) span.classList.add("sb-empty-value");

        field.appendChild(span);
        container.appendChild(field);
      });
    } else {
      const emptyField = elementCreator({
        type: "div",
        params: { className: "sb-main-item" },
      });
      emptyField.appendChild(Label(lang.get("no-main-fields")));
      container.appendChild(emptyField);
    }

    return container;
  }

  createPasswordFields(fields) {
    const container = elementCreator({
      type: "div",
      params: { className: "sb-password" },
    });

    if (fields !== "empty") {
      Object.entries(fields).forEach(([key, value]) => {
        const field = elementCreator({
          type: "div",
          params: { className: "sb-password-item" },
        });

        const isEmpty = value === "empty" || value === "";

        if (isEmpty) {
          const span = Span({
            innerText: lang.get("no-data"),
          });
          span.classList.add("sb-main-span", "sb-empty-value");
          field.appendChild(span);
        } else {
          const passField = PasswordField({ value });
          passField.classList.add("sb-password-field");
          field.appendChild(passField);
        }

        container.appendChild(field);
      });
    } else {
      const emptyField = elementCreator({
        type: "div",
        params: { className: "sb-password-item" },
      });
      emptyField.appendChild(Label(lang.get("no-password-fields")));
      container.appendChild(emptyField);
    }

    return container;
  }

  async showId(id, buttonName, old_line) {
    try {
      Showindicator(old_line);
      const callback = await sendRequest({
        type: "queries",
        job: "select id",
        button: buttonName,
        id: id,
      });

      const holder = home.WorkDiv;
      holder.innerHTML = "";

      const formData = {};

      Object.entries(callback.data).forEach(([key, value]) => {
        if (key === "id") return;

        const line = lineCreator("sb-detail");
        const updateBtn = Button({
          innerText: lang.get("save-btn"),
          className: "sb-update-btn",
          onclick: async () => {
            Showindicator(line);
            const res = await sendRequest({
              type: "queries",
              job: "update value",
              button: buttonName,
              id,
              column: key,
              value: formData[key],
            });

            if (res?.status === "successful") {
              indicatorRemover();
              showNotification(lang.get("notification-update"));
            } else {
              indicatorRemover();
              showNotification(lang.get("failed-to-update"));
            }
          },
        });
        const input = Input({
          name: key,
          value: value,
          placeholder: key,
          ondblclick: copyToClipboard,
          onkeydown: (e) => Clicker(e, updateBtn),
        });

        input.oninput = () => (formData[key] = input.value);
        formData[key] = value;

        line.appendChild(updateBtn);
        line.appendChild(input);
        line.appendChild(Label(key));
        holder.appendChild(line);
      });

      const deleteBtn = Button({
        innerText: lang.get("delete"),
        className: "sb-delete-btn",
        onclick: async () => {
          if (!confirm(lang.get("delete-btn-confirm"))) return;
          Showindicator(holder);
          const res = await sendRequest({
            type: "queries",
            job: "delete id",
            button: buttonName,
            id,
          });
          if (res?.status === "successful") {
            showNotification(lang.get("notification-delete"));
            new ShowButton(buttonName);
          } else {
            showNotification(lang.get("failed-to-delete"));
          }
        },
      });

      holder.append(deleteBtn);
    } catch (error) {
      console.error(error);
      Message(lang.get("loading-error"));
    }
  }
}
