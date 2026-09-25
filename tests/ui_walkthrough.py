import asyncio, sys
from playwright.async_api import async_playwright

BASE = "http://127.0.0.1:8090"
OUT = "/app/kantinsmk-go/tests/screens"

async def login(page, email, pw):
    await page.goto(f"{BASE}/login")
    await page.fill('[data-testid="login-email-input"]', email)
    await page.fill('[data-testid="login-password-input"]', pw)
    await page.click('[data-testid="login-submit-button"]')
    await page.wait_for_load_state("networkidle")

async def shot(page, name, mobile=False):
    await page.wait_for_timeout(600)
    await page.screenshot(path=f"{OUT}/{name}.png", full_page=False)
    print("shot", name)

async def main():
    async with async_playwright() as p:
        browser = await p.chromium.launch(executable_path="/usr/bin/google-chrome", args=["--no-sandbox"])
        # --- MOBILE buyer flow ---
        ctx = await browser.new_context(viewport={"width": 390, "height": 844}, device_scale_factor=1)
        page = await ctx.new_page()
        errors = []
        page.on("pageerror", lambda e: errors.append(str(e)))
        page.on("console", lambda m: errors.append(m.text) if m.type == "error" else None)
        await page.goto(f"{BASE}/login"); await shot(page, "m1-login")
        await login(page, "siswa@smkgo.id", "buyer123"); await shot(page, "m2-catalog")
        await page.click('[data-testid="add-product-1-button"]'); await page.wait_for_timeout(700)
        await page.click('[data-testid="add-product-1-button"]'); await page.wait_for_timeout(700)
        await page.click('[data-testid="add-product-5-button"]'); await page.wait_for_timeout(900)
        badge = await page.locator('[data-testid="cart-badge"]').first.inner_text()
        print("cart badge:", badge)
        await shot(page, "m3-catalog-added")
        await page.click('[data-testid="bottom-nav-cart"]'); await page.wait_for_load_state("networkidle"); await shot(page, "m4-cart")
        await page.click('[data-testid="checkout-button"]'); await page.wait_for_load_state("networkidle"); await shot(page, "m5-checkout")
        await page.click('[data-testid="confirm-order-button"]'); await page.wait_for_url("**/invoice/**"); await shot(page, "m6-invoice-qris")
        await page.click('[data-testid="simulate-payment-button"]'); await page.wait_for_timeout(1200); await shot(page, "m7-invoice-paid")
        await page.click('[data-testid="bottom-nav-orders"]'); await page.wait_for_load_state("networkidle"); await shot(page, "m8-orders")
        await page.locator('[data-testid^="order-card-"]').first.click(); await page.wait_for_load_state("networkidle"); await shot(page, "m9-order-detail")
        await ctx.close()

        # --- DESKTOP tenant + admin ---
        ctx = await browser.new_context(viewport={"width": 1440, "height": 900})
        page = await ctx.new_page()
        page.on("pageerror", lambda e: errors.append(str(e)))
        await login(page, "staff@bu-rina.id", "staff123"); await shot(page, "d1-staff-board")
        adv = page.locator('[data-testid^="advance-order-"]').first
        await adv.click(); await page.wait_for_timeout(1000); await shot(page, "d2-staff-advanced")
        await page.click('[data-testid="logout-button"]'); await page.wait_for_load_state("networkidle")
        await login(page, "owner@bu-rina.id", "owner123"); await shot(page, "d3-owner-dashboard")
        await page.click('[data-testid="side-nav-tenant.products"]'); await page.wait_for_load_state("networkidle"); await shot(page, "d4-owner-products")
        await page.click('[data-testid="add-menu-button"]'); await page.wait_for_timeout(600); await shot(page, "d5-owner-product-form")
        await page.click('[data-testid="close-form-button"]')
        await page.click('[data-testid="side-nav-tenant.payments"]'); await page.wait_for_load_state("networkidle"); await shot(page, "d6-owner-payments")
        await page.click('[data-testid="logout-button"]'); await page.wait_for_load_state("networkidle")
        await login(page, "admin@smkgo.id", "admin123"); await shot(page, "d7-admin-dashboard")
        await page.click('[data-testid="side-nav-admin.users"]'); await page.wait_for_load_state("networkidle"); await shot(page, "d8-admin-users")
        await page.click('[data-testid="side-nav-admin.tenants"]'); await page.wait_for_load_state("networkidle"); await shot(page, "d9-admin-tenants")
        await page.click('[data-testid="side-nav-admin.transactions"]'); await page.wait_for_load_state("networkidle"); await shot(page, "d10-admin-tx")
        # desktop buyer catalog
        await page.click('[data-testid="logout-button"]'); await page.wait_for_load_state("networkidle")
        await login(page, "guru@smkgo.id", "guru123"); await shot(page, "d11-buyer-desktop")
        await browser.close()
        print("JS errors:", errors)

asyncio.run(main())
