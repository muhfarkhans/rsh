document.addEventListener("DOMContentLoaded", function () {
    const printButton = document.getElementById("print-struk-btn");
    const printer = new Recta("APPKEY", "1811");

    if (printButton) {
        printButton.addEventListener("click", function () {
            const data = {
                invoiceId: printButton.getAttribute("data-invoice-id"),
                clientName: printButton.getAttribute("data-client-name"),
                cashierName: printButton.getAttribute("data-cashier-name"),
                createdAt: printButton.getAttribute("data-created-at"),
                serviceName: printButton.getAttribute("data-service-name"),
                amountService: printButton.getAttribute("data-amount-service"),
                amountAddName: printButton.getAttribute("data-amount-add-name"),
                amountAdd: printButton.getAttribute("data-amount-add"),
                discountName: printButton.getAttribute("data-discount-name"),
                discountPrice: printButton.getAttribute("data-discount-price"),
                total: printButton.getAttribute("data-total"),
                paymentMethod: printButton.getAttribute("data-payment-method"),
            };

            printer.open().then(function () {
                printer
                    .align("center")
                    .bold(true)
                    .text("Rumah Sehat Holistik")
                    .text("Islami & Integratif")
                    .text(
                        "Jl. Raya Wisma Pagesangan No.79, Pagesangan, Kec. Jambangan, Surabaya, Jawa Timur 60233"
                    )
                    .text("---------------------------")
                    .bold(false)
                    .align("left")
                    .text("Invoice ID: " + data.invoiceId)
                    .text("Tanggal   : " + data.createdAt)
                    .text("Pelanggan : " + data.clientName)
                    .text("Kasir     : " + data.cashierName)
                    .text("---------------------------")
                    .text("Layanan   : " + data.serviceName)
                    .text("Harga     : Rp " + data.amountService)
                    .text("Add-On    : " + data.amountAddName)
                    .text("Harga     : Rp " + data.amountAdd)
                    .text("Diskon    : " + (data.discountName || "-"))
                    .text("Potongan  : Rp " + data.discountPrice)
                    .text("---------------------------")
                    .bold(true)
                    .text("TOTAL     : Rp " + data.total)
                    .bold(false)
                    .text("Metode Pembayaran: " + data.paymentMethod)
                    .text("")
                    .align("center")
                    .text("Terima kasih!")
                    .cut()
                    .print();
            });
        });
    }
});
