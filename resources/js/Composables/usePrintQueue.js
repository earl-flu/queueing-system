export function usePrintQueue() {
    const printQueueTicket = ({
        queueNumber,
        firstName,
        lastName,
        isPriority,
        flowDepartments,
        queueDate,
    }) => {
        const formatName = (firstName, lastName) => {
            if (
                typeof firstName !== "string" ||
                typeof lastName !== "string" ||
                firstName.trim().length === 0 ||
                lastName.trim().length === 0
            ) {
                return "Invalid name";
            }

            const formattedFirstName = firstName
                .trim()
                .split(/\s+/)
                .map((part) => {
                    if (part.length <= 1) {
                        return part.charAt(0);
                    }
                    const firstLetter = part.charAt(0);
                    const lastLetter = part.slice(-1);
                    const middleAsterisks = "•".repeat(part.length - 2);
                    return firstLetter + middleAsterisks + lastLetter;
                })
                .join(" ");

            const formattedLastName =
                lastName.trim().charAt(0).toUpperCase() + ".";

            return `${formattedFirstName} ${formattedLastName}`;
        };

        const formattedName = formatName(firstName, lastName);

        const spanQueueNumber = isPriority
            ? `<span style="color:red;">${queueNumber}</span>`
            : `<span>${queueNumber}</span>`;

        const dateObject = new Date(queueDate);
        const formatter = new Intl.DateTimeFormat("en-US", {
            month: "2-digit",
            day: "2-digit",
            year: "2-digit",
            hour: "numeric",
            minute: "2-digit",
            hour12: true,
        });
        const formattedDate = formatter.format(dateObject);

        const stepsHtml = flowDepartments
            .map((dept) => `<li>${dept.department_name}</li>`)
            .join("");

        // ✅ Create hidden iframe for printing (works on mobile too)
        const iframe = document.createElement("iframe");
        iframe.style.position = "absolute";
        iframe.style.width = "0";
        iframe.style.height = "0";
        iframe.style.border = "0";
        document.body.appendChild(iframe);

        iframe.contentDocument.open();
        iframe.contentDocument.write(`
        <html>
          <head><title>Queue Number</title></head>
          <body style="margin:0;">
            <div style="
                margin:0 auto;
                width:220px;
                height:340px; 
                border:1px solid black;
                font-family: montserrat;
                text-align: center;
                box-sizing: border-box;">
              <div style="padding:15px; 
                  display:flex;
                  flex-direction:column; 
                  box-sizing:border-box;
                  height:100%;
                  justify-content: space-between;">
                  <h3 style="margin:0;">Your OPD Number:</h3>
                  <h2 style="font-size:55px; margin:0; font-family: sans-serif; line-height:100%;">${spanQueueNumber}</h2>
                  <p style="margin:0; margin-top:-30px; font-size:12; text-transform:uppercase;">${formattedName}</p>
                  <div style="font-size:13px;">
                      <p style="margin:0;">Steps:</p>
                      <ol style="text-align: left; margin:0;">
                         ${stepsHtml}
                      </ol>
                  </div>
                    <div>
                        <p style="margin: 0; font-size:12px;">Please be seated. <br>You will be served shortly.</p>
                        <p style="color:#6e877b; font-size:10px; margin:0;">Generated: ${formattedDate}</p>
                    </div>
                </div>
            </div>
            <script>
              window.onload = function() {
                window.print();
                setTimeout(() => window.close(), 100);
              }
            <\/script>
          </body>
        </html>
      `);
        iframe.contentDocument.close();
    };

    return { printQueueTicket };
}
