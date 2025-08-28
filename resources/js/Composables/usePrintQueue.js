export function usePrintQueue() {
    const printQueueTicket = ({
        queueNumber,
        isPriority,
        flowDepartments,
        queueDate,
    }) => {
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

        const printWindow = window.open("", "_blank", "width=950,height=600");
        printWindow.document.write(`
        <html>
          <head><title>Queue Number</title></head>
          <body>
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
                  <h2 style="font-size:55px; margin:0; font-family: sans-serif">${spanQueueNumber}</h2>
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
            <script>window.print(); window.close();<\/script>
          </body>
        </html>
      `);
    };

    return { printQueueTicket };
}
