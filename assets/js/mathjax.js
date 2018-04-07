window.MathJax = {
  extensions: ['tex2jax.js'],
  jax: ['input/TeX'],
  tex2jax: {
    // Array of pairs of strings that are to be used as in-line math delimiters
    inlineMath: [ ['\\(','\\)'] ],
    // Array of pairs of strings that are to be used as delimiters for displayed equations.
    displayMath: [ ['\\[','\\]'] ],
    // Allow nested expressions such as $y = x^2 \hbox{ when $x > 2$}$
    balanceBraces: true,
    // Don't process escaped symbols such as \$
    processEscapes: true,
    // Look for LaTeX environments outside of math mode
    processEnvironments: true,
    // Process \ref{...} outside of math mode.
    processRefs: true,
    // Ignore tags whose contents should not be processed
    skipTags: ["script", "noscript", "style", "textarea", "pre", "code"],
    // Ignore classes whose contents should not be processed
    ignoreClass: 'ignore-latex',
    // Class name used to mark elements whose contents should be processed
    processClass: 'latex'
  },
  showProcessingMessages: false,
  messageStyle: 'none'
};
