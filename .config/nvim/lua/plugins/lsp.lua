return {
  {
    "neovim/nvim-lspconfig",
    opts = {
      servers = {
        intelephense = {},
        -- or use phpactor if you prefer:
        -- phpactor = {},
      },
    },
  },
}
