/**
 * Mini biblioteca de gráfico de barras em Canvas puro (sem dependências externas).
 * Uso: criarGraficoBarras('idDoCanvas', { labels: [...], valores: [...], cores: [...] });
 */
function criarGraficoBarras(canvasId, dados) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const largura = canvas.clientWidth || canvas.parentElement.clientWidth;
    const altura = canvas.height || 90;

    canvas.width = largura * dpr;
    canvas.height = altura * dpr;
    canvas.style.width = largura + 'px';
    canvas.style.height = altura + 'px';
    ctx.scale(dpr, dpr);

    const { labels, valores, cores } = dados;
    const margemInferior = 24;
    const margemSuperior = 10;
    const areaUtil = altura - margemInferior - margemSuperior;
    const maximo = Math.max(1, ...valores);
    const larguraBarra = (largura / labels.length) * 0.5;
    const espacamento = largura / labels.length;

    ctx.clearRect(0, 0, largura, altura);
    ctx.font = '12px Arial';
    ctx.textAlign = 'center';

    valores.forEach((valor, i) => {
        const alturaBarra = (valor / maximo) * areaUtil;
        const x = espacamento * i + (espacamento - larguraBarra) / 2;
        const y = margemSuperior + (areaUtil - alturaBarra);

        ctx.fillStyle = (cores && cores[i]) || '#0d6efd';
        ctx.fillRect(x, y, larguraBarra, alturaBarra);

        ctx.fillStyle = '#333';
        ctx.fillText(valor, x + larguraBarra / 2, y - 4);
        ctx.fillText(labels[i], x + larguraBarra / 2, altura - 6);
    });
}