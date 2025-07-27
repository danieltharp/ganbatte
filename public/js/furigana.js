// Adapted from https://github.com/steven-kraft/obsidian-markdown-furigana
const REGEXP = /{((?:[\u2E80-\uA4CF\uFF00-\uFFEF])+)((?:\|[^ -\/{-~:-@\[-`]*)+)}/gm;
// Main Tags to search for Furigana Syntax
const TAGS = "p, h1, h2, h3, h4, h5, h6, ol, ul, table";
function convertFurigana(element) {
    const matches = Array.from(element.textContent.matchAll(REGEXP));
    let lastNode = element;
    for (const match of matches) {
        const furi = match[2].split("|").slice(1); // First Element will be empty
        const kanji = furi.length === 1 ? [match[1]] : match[1].split("");
        if (kanji.length === furi.length) {
            // Number of Characters in first section must be equal to number of furigana sections (unless only one furigana section)
            const rubyNode = document.createElement("ruby");
            rubyNode.classList.add("furigana");
            kanji.forEach((k, i) => {
                rubyNode.insertAdjacentHTML("beforeend",k + "<rt>" + furi[i] + "</rt>");
            });
            const nodeToReplace = lastNode.splitText(lastNode.textContent.indexOf(match[0]));
            lastNode = nodeToReplace.splitText(match[0].length);
            nodeToReplace.replaceWith(rubyNode);
        }
    }
    return element;
};

function renderFurigana(el) {
            const blockToReplace = el.querySelectorAll(TAGS);
            if (blockToReplace.length === 0)
                return;
            function replace(node) {
                const childrenToReplace = [];
                node.childNodes.forEach(child => {
                    if (child.nodeType === 3) {
                        // Nodes of Type 3 are TextElements
                        childrenToReplace.push(child);
                    }
                    else if (child.hasChildNodes() && child.nodeName !== "CODE" && child.nodeName !== "RUBY") {
                        // Ignore content in Code Blocks
                        replace(child);
                    }
                });
                childrenToReplace.forEach((child) => {
                    child.replaceWith(convertFurigana(child));
                });
            }
            blockToReplace.forEach(block => {
                replace(block);
            });
        };

function toggleFurigana() {
    var rubies = document.querySelectorAll(".furigana");
    for(let ruby of rubies) {
        ruby.classList.toggle("no-furigana");
    };
}

window.onload = function() { renderFurigana(document.body); };