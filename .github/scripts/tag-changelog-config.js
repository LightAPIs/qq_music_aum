module.exports = {
  types: [
    {
      types: ['feat', 'feature'],
      label: '新功能',
    },
    {
      types: ['fix', 'bugfix'],
      label: '问题修复',
    },
    {
      types: ['perf'],
      label: '优化',
    },
    {
      types: ['refactor'],
      label: '重构',
    },
    {
      types: ['build', 'ci'],
      label: '构建',
    },
  ],
  excludeTypes: ['other', 'docs', 'style', 'test', 'chore'],
};
