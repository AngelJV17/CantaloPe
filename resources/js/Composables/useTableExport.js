import { ref } from "vue";
import { jsPDF } from "jspdf";
import html2canvas from "html2canvas";

export function useTableExport() {
    const isDownloading = ref(false);

    const downloadPDF = async (elementId, fileName) => {
        const element = document.getElementById(elementId);
        if (!element) return;

        isDownloading.value = true;

        try {
            // Esperamos un momento para que el DOM se asiente
            await new Promise((resolve) => setTimeout(resolve, 200));

            const canvas = await html2canvas(element, {
                scale: 3,
                useCORS: true,
                allowTaint: true,
                backgroundColor: "#12141c", // Aseguramos el fondo oscuro
            });

            const imgData = canvas.toDataURL("image/jpeg", 1.0);
            const pdf = new jsPDF({
                orientation: "portrait",
                unit: "mm",
                format: "a5",
            });

            pdf.addImage(imgData, "JPEG", 0, 0, 148, 210);
            pdf.save(`${fileName}.pdf`);
        } catch (e) {
            console.error("Error al exportar PDF:", e);
        } finally {
            isDownloading.value = false;
        }
    };

    return { downloadPDF, isDownloading };
}
