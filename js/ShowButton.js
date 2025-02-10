class ShowButton {
  static get Styel() {
    return {
      mainDiv: {
        padding: "1%",
        width: "100%",
        height: "100%",
        overflow: "auto",
      },
      line: {
        margin: "1.5% auto",
        "border-radius": "8px",
        "background-color": "rgba(0, 0, 0, 0.521)",
      },
      mainHolder: {
        padding: "1%",
        display: "flex",
        "justify-content": "center",
        "align-items": "center",
        "flex-wrap": "wrap",
        width: "50%",
        height: "100%",
        overflow: "hidden",
      },
      mainItem: {
        padding: "1%",
        display: "flex",
        "justify-content": "center",
        "align-items": "center",
        "flex-wrap": "wrap",
        width: "100%",
        height: "100%",
      },
      mainItemSpan: {
        "text-align": "center",
        contain: "content",
        color: "wheat",
        width: "80%",
      },
      label: {
        width: "20%",
      },
      passwordsHolder: {
        width: "50%",
        height: "100%",
      },
      passwordItem: {
        display: "flex",
        "justify-content": "center",
        "align-items": "center",
        width: "100%",
        height: "100%",
      },
      PasswordField: {
        backgroundColor: "transparent",
      },
      detailsButton: {
        cursor: "pointer",
        color: "wheat",
        width: "15%",
        height: "100%",
        backgroundColor: "transparent",
      },
      ShowButtonDetails: {
        cursor: "pointer",
        color: "wheat",
        width: "15%",
        height: "100%",
        backgroundColor: "transparent",
      },
      ShowButtonDetailsLabel: {
        width: "50%",
        color: "wheat",
      },
    };
  }
  constructor(event) {
    const buttonName = event.target ? event.target.innerText : event;
    Showindicator(findButtonByText(buttonName));
    this.loadData(buttonName);
  }

  async loadData(buttonName) {
    try {
      const callback = await sendRequest({
        type: "queries",
        job: "show Button",
        button: buttonName,
      });
      indicatorRemover();
      if (callback.response === "no data")
        return displayEmptyMessage("لا توجد معلومات");
      if (callback.response === "no columns")
        return displayEmptyMessage("لا توجد أعمدة في هذا الزر");

      const holderDiv = cleanWorkDiv();
      SetStyle(holderDiv, ShowButton.Styel.mainDiv);

      callback.data.forEach((data) => {
        const line = lineCreator();
        SetStyle(line, ShowButton.Styel.line);

        const mainContent = this.createMainContent(data.main || "empty");
        line.appendChild(mainContent);

        const passwordContent = this.createPasswordFields(
          data.passwords || "empty"
        );
        line.appendChild(passwordContent);

        const detailsButton = Button({
          innerText: "تفاصيل",
          onclick: () => this.showId(data.id, buttonName),
        });
        SetStyle(detailsButton, ShowButton.Styel.detailsButton);

        line.appendChild(detailsButton);

        holderDiv.appendChild(line);
      });
    } catch (error) {
      console.error("Error loading button data:", error);
      this.displayEmptyMessage("حدث خطأ أثناء تحميل البيانات");
    }
  }

  createMainContent(mains) {
    if (mains === "empty") {
      return this.createMessageElement("لا يتواجد أعمدة");
    }

    const mainHolder = document.createElement("div");
    SetStyle(mainHolder, ShowButton.Styel.mainHolder);

    Object.entries(mains).forEach(([key, value]) => {
      const wrapper = document.createElement("div");
      SetStyle(wrapper, ShowButton.Styel.mainItem);

      wrapper.appendChild(Label(key));

      const mainValue = Span({ innerText: value, ondblclick: copyToClipboard });
      SetStyle(mainValue, ShowButton.Styel.mainItemSpan);

      wrapper.appendChild(mainValue);
      mainHolder.appendChild(wrapper);
    });

    return mainHolder;
  }

  createPasswordFields(passwords) {
    if (passwords === "empty") {
      return this.createMessageElement("لا يتوجد أعمدة");
    }

    const passwordHolder = document.createElement("div");
    SetStyle(passwordHolder, ShowButton.Styel.passwordsHolder);

    Object.entries(passwords).forEach(([key, value]) => {
      const wrapper = document.createElement("div");
      SetStyle(wrapper, ShowButton.Styel.passwordItem);

      wrapper.appendChild(Label(key));

      const passwordValue = PasswordField({ type: "password", value: value });
      SetStyle(passwordValue, ShowButton.Styel.PasswordField);

      wrapper.appendChild(passwordValue);
      passwordHolder.appendChild(wrapper);
    });

    return passwordHolder;
  }

  async showId(id, buttonName) {
    try {
      const callback = await sendRequest({
        type: "queries",
        job: "select id",
        button: buttonName,
        id: id,
      });

      const holderDiv = home.WorkDiv;
      holderDiv.innerHTML = "";

      Object.entries(callback.data).forEach(([key, value]) => {
        if (key === "id") return;

        const line = lineCreator("ShowButton-details");
        SetStyle(line, ShowButton.Styel.ShowButtonDetails);

        line.appendChild(Label(key));

        const valueSpan = Span({ innerText: value });

        line.appendChild(valueSpan);
        holderDiv.appendChild(line);
      });
    } catch (error) {
      console.error("Error fetching details:", error);
      this.displayEmptyMessage("حدث خطأ أثناء تحميل التفاصيل");
    }
  }

  createMessageElement(message) {
    const messageDiv = document.createElement("div");
    messageDiv.innerText = message;
    messageDiv.classList.add("empty-message");
    return messageDiv;
  }
}
