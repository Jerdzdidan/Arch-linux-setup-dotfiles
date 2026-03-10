local lspconfig = require("lspconfig")

lspconfig.intelephense.setup({
  on_attach = function(client, bufnr)
    -- optional: map keys or settings for Laravel
    -- example: enable formatting with Pint
    if client.name == "intelephense" then
      vim.api.nvim_buf_set_option(bufnr, "formatexpr", "v:lua.vim.lsp.formatexpr()")
    end
  end,
  root_dir = lspconfig.util.root_pattern("artisan", "composer.json", ".git"),
  settings = {
    intelephense = {
      files = { maxSize = 5000000 }, -- handle big Laravel projects
    },
  },
})
