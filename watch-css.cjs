/**
 * PAMS — CSS Sync Watcher
 * Watches resources/css/ and auto-copies any change to public/css/.
 * Run with: node watch-css.js
 * 
 * No npm packages required — uses Node's built-in fs.watch().
 */

const fs   = require('fs');
const path = require('path');

const SRC  = path.join(__dirname, 'resources', 'css');
const DEST = path.join(__dirname, 'public', 'css');

function sync(relativePath) {
    const src  = path.join(SRC, relativePath);
    const dest = path.join(DEST, relativePath);

    // Ensure the destination directory exists
    fs.mkdirSync(path.dirname(dest), { recursive: true });

    try {
        fs.copyFileSync(src, dest);
        const time = new Date().toLocaleTimeString();
        console.log(`[${time}] synced → public/css/${relativePath}`);
    } catch (err) {
        console.error(`[ERROR] could not sync ${relativePath}:`, err.message);
    }
}

function watchDir(dir, baseDir) {
    baseDir = baseDir || dir;

    fs.readdirSync(dir, { withFileTypes: true }).forEach(entry => {
        const fullPath = path.join(dir, entry.name);
        if (entry.isDirectory()) {
            watchDir(fullPath, baseDir);
        }
    });

    fs.watch(dir, { recursive: false }, (eventType, filename) => {
        if (!filename || !filename.endsWith('.css')) return;

        const relative = path.relative(baseDir, path.join(dir, filename));
        // Small debounce — editors write files in bursts
        setTimeout(() => sync(relative), 50);
    });
}

console.log('');
console.log('  PAMS CSS Watcher');
console.log('  ─────────────────────────────────────');
console.log('  Watching: resources/css/');
console.log('  Syncing to: public/css/');
console.log('  Press Ctrl+C to stop.');
console.log('');

// Do an initial full sync on start
function fullSync(dir, baseDir) {
    baseDir = baseDir || dir;
    fs.readdirSync(dir, { withFileTypes: true }).forEach(entry => {
        const fullPath = path.join(dir, entry.name);
        if (entry.isDirectory()) {
            fullSync(fullPath, baseDir);
        } else if (entry.name.endsWith('.css')) {
            sync(path.relative(baseDir, fullPath));
        }
    });
}

fullSync(SRC);
watchDir(SRC, SRC);
