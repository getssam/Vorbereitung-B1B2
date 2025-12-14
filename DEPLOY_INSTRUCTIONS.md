# Guide de Déploiement / Deployment Guide (Derija/English)

Project dialek fih **Frontend** (HTML/CSS/JS) w **Backend** (Node.js/Express).
Netlify hwa host wa3er l **Frontend**, walakin maikhedemch lik **Backend** (li fih database w sessions) bhal server normal.

Khessk tferreghom:
1. **Hosting Backend**: Dirou f **Render** (wla Railway/Glitch).
2. **Hosting Frontend**: Dirou f **Netlify**.

Hna chnou khassek dir step-by-step:

## Step 1: Backend (Render)
1.  Hett project dialek f GitHub (ila mamhtoutch).
2.  Sir l [Render.com](https://render.com) w create "Web Service".
3.  Connectih m3a repo dialek.
4.  F settings dial Render:
    *   **Root Directory**: `backend`
    *   **Build Command**: `npm install`
    *   **Start Command**: `node server.js`
5.  Zid Environment Variables (Environment -> Add Environment Variable):
    *   `SESSION_SECRET`: Chi mot de passe s3ib (e.g., `s3cr3t_k3y_for_s3ss1ons`)
    *   `CORS_ORIGIN`: `https://YOUR-SITE-NAME.netlify.app` (Hna ghatji men be3d dir l'URL dyal Netlify mli tcreéh, owl dir `*` mo2aqqatan).
6.  Click **Create Web Service**.
7.  Copy l'URL li ya3tik Render (e.g., `https://my-app-backend.onrender.com`).

**Note Important**: Hit katsta3mel `SQLite` file (`database.sqlite`), f Free Tier dial Render, kula marra redémare server (awla derti deploy jdid), database katarja3 zero (kad3i data). Ila bghiti data tbqa, khassek tekhdem b database bhal PostgreSQL wla tsta3mel host li kaykheli disk (bhal Railway volume wla VM).

## Step 2: Configure Project Code
1.  Reje3 l dossier dial project.
2.  Hel Fichier `netlify.toml` (wla `_redirects`).
3.  Bdel `https://YOUR_BACKEND_URL_HERE` b l'URL li khditi men Render.
    *   Example f `netlify.toml`:
        ```toml
        [[redirects]]
          from = "/api/*"
          to = "https://my-app-backend.onrender.com/api/:splat"
          status = 200
          force = true
        ```

## Step 3: Frontend (Netlify)
1.  Sir l [Netlify.com](https://netlify.com).
2.  Dkhel w dir "Add new site" -> "Import an existing project" (men GitHub) AWLA "Deploy manually" (jerr dossier kamel l page dial Netlify Drop).
3.  Ila derti Import men GitHub:
    *   **Build command**: (khalliha khawya)
    *   **Publish directory**: `.` (point/noqt dial root, awla kheliha khawya).
4.  Netlify ghadi yqra `netlify.toml` w yfehem redirects.
5.  Mli tsali, l site ghadi ykhedem!
    *   Frontend ghadi ycharja men Netlify.
    *   Login/Register ghadi ydouzou l Render via proxy.

## Step 4: Final Touch
1.  Reje3 l Render dashboard.
2.  Update `CORS_ORIGIN` variable bach ykoun hwa URL dial Netlify dialek (bach t'sécurisiha).

## Limitation (Quiz Dark Mode)
Backend dialek kan kayzid "Dark Mode Script" f les fichiers quiz (`/quiz/*.html`) dynamique. Hit db Frontend static, dak script maghadich ykoun.
Solution: Khassek tzid dak script manuel f les fichiers HTML wla tqbel bli quiz ghadi ykouno b thema 3adiya. 
(Ila bghiti script bach yzido automatiquement, goulha liya).

---
Hadchi li kain! Project dialek db "Production Ready" (m3a hadok limitations).
