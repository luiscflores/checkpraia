(function() {
  'use strict';

  const panel = (() => {
    for (const sel of ['#wave-panel', '#wave-sidebar', '#wAVE', '.wave-panel', '#wave-toolbar', '.wave-toolbar']) {
      const el = document.querySelector(sel);
      if (el) return el;
    }
    for (const el of document.querySelectorAll('div, aside, section')) {
      if ((el.textContent || '').includes('WAVE') && el.offsetWidth > 100 &&
        el.querySelectorAll('[class*="category"], [class*="summary"], [class*="count"]').length > 0) {
        return el;
      }
    }
    return null;
  })();

  if (!panel) {
    console.error('WAVE panel not found. Make sure WAVE extension is active on this page.');
    return;
  }

  const findings = [];
  let id = 0;

  panel.querySelectorAll('section, .category, [class*="category"], .wave-section, details, .accordion-panel').forEach(section => {
    const header = section.querySelector('h1, h2, h3, h4, summary, [class*="header"]');
    const category = header ? header.textContent.trim() : 'Unknown';

    section.querySelectorAll('li, .item, .finding, [class*="finding"], .entry, .result, tr').forEach(item => {
      const text = item.textContent.replace(/\s+/g, ' ').trim();
      if (text && text.length > 4) {
        const icon = item.querySelector('img, svg, [class*="icon"], [class*="badge"]');
        const iconInfo = icon
          ? (icon.getAttribute('alt') || icon.getAttribute('aria-label') || icon.className || 'icon')
          : '';
        id++;
        findings.push({ id, category, icon: iconInfo, text });
      }
    });
  });

  if (!findings.length) {
    panel.querySelectorAll('*').forEach(el => {
      if (el.children.length === 0 && el.textContent.trim().length > 4) {
        const style = window.getComputedStyle(el);
        if (style.display !== 'none' && style.visibility !== 'hidden') {
          id++;
          findings.push({ id, category: 'unknown', icon: '', text: el.textContent.trim().replace(/\s+/g, ' ') });
        }
      }
    });
  }

  if (!findings.length) {
    console.log('No structured findings. Panel HTML snippet:');
    console.log(panel.innerHTML.substring(0, 3000));
    return;
  }

  const summary = panel.querySelector('[class*="summary"], [class*="count"], [class*="stats"]');
  if (summary) {
    console.log(`WAVE Summary: ${summary.textContent.trim().replace(/\s+/g, ' ')}`);
  }

  console.log(`\nWAVE Findings (${findings.length} total):\n`);
  findings.forEach(f => {
    console.log(`#${f.id} [${f.category}]${f.icon ? ' <' + f.icon + '>' : ''}`);
    console.log(`   ${f.text}`);
    console.log('');
  });

  console.log('═══════════════════════════════════════');
  console.log(`Total: ${findings.length} findings`);
  console.log('═══════════════════════════════════════');

  console.log('\nJSON export:');
  console.log(JSON.stringify(findings, null, 2));
})();
