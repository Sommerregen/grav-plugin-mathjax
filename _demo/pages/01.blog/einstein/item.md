---
title: Einstein Field Equations
taxonomy:
	category: blog

mathjax:
  process: true
---
<p></p>

The Einstein field equations (EFE) may be written in the form:

\[
R_{\mu \nu} - {1 \over 2} g_{\mu \nu}\,R + g_{\mu \nu} \Lambda = {8 \pi G \over c^4} T_{\mu \nu}
\]

where $R_{\mu \nu}$, is the [_Ricci curvature tensor_](https://en.wikipedia.org/wiki/Ricci_curvature_tensor), $g_{\mu \nu}$, is the [_metric tensor_](https://en.wikipedia.org/wiki/Metric_tensor_(general_relativity)), $\Lambda$, is the [_cosmological constant_](https://en.wikipedia.org/wiki/Gravitational_constant), $G$, is [_Newton's gravitational constant_](https://en.wikipedia.org/wiki/Gravitational_constant), $c$, is the [speed of light](https://en.wikipedia.org/wiki/Speed_of_light) in vacuum, $R$, is the [_scalar curvature_](https://en.wikipedia.org/wiki/Scalar_curvature) and $T_{\mu \nu}$, is the [_stress-energy tensor_](https://en.wikipedia.org/wiki/Stress%E2%80%93energy_tensor).

The EFE is a tensor equation relating a set of symmetric 4x4 tensors. Each tensor has 10 independent components. The four Bianchi identities reduce the number of independent equations from 10 to 6, leaving the metric with four gauge fixing degrees of freedom, which correspond to the freedom to choose a coordinate system.

#### Sign convention

The above form of the EFE is the standard established by _Misner, Thorne, and Wheeler_. The authors analyzed all conventions that exist and classified according to the following three signs (S1, S2, S3):

$$
\begin{align}
	g_{\mu \nu} & = [S1] \times \operatorname{diag}(-1,+1,+1,+1) \\[6pt]
	{R^\mu}_{\alpha \beta \gamma} & = [S2] \times (\Gamma^\mu_{\alpha \gamma,\beta}-\Gamma^\mu_{\alpha \beta,\gamma}+\Gamma^\mu_{\sigma \beta}\Gamma^\sigma_{\gamma \alpha}-\Gamma^\mu_{\sigma \gamma}\Gamma^\sigma_{\beta \alpha}) \\[6pt]
	G_{\mu \nu} & = [S3] \times {8 \pi G \over c^4} T_{\mu \nu}
\end{align}
$$

The third sign above is related to the choice of convention for the Ricci tensor:

$$
R_{\mu \nu}=[S2]\times [S3] \times {R^\alpha}_{\mu\alpha\nu}
$$

With these definitions Misner, Thorne, and Wheeler classify themselves as $(+++)$, whereas Weinberg (1972) is $(+--)$, Peebles (1980) and Efstathiou (1990) are $(-++)$, while Peacock (1994), Rindler (1977), Atwater (1974), Collins Martin & Squires (1989) are $(-+-)$.
